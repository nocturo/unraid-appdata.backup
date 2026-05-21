<?php

use unraid\plugins\AppdataBackup\ABHelper;
use unraid\plugins\AppdataBackup\ABSettings;

/** @var $abSettings ABSettings */

if (!ABHelper::isArrayOnline()) {
    echo "<h1>Oooopsie!</h1><p>The array is NOT online!</p>";
    return;
}

?>

<style>
    /* Match settings page: full-width section titles; grid only on form rows (overrides webGUI floats) */
    #restoreForm dt {
        width: 54%;
    }

    #restoreForm dl {
        display: grid !important;
        grid-template-columns: minmax(9rem, 54%) minmax(0, 1fr);
        column-gap: 0.75rem;
        align-items: start;
        width: 100%;
        max-width: 100%;
        margin: 0 0 0.5rem 0;
        padding: 0;
        box-sizing: border-box;
        clear: both;
        float: none !important;
    }

    #restoreForm dl > dt,
    #restoreForm dl > dd {
        float: none !important;
        width: auto !important;
        max-width: 100%;
        min-width: 0;
        margin: 0 0 0.65rem 0;
        padding: 0;
        box-sizing: border-box;
        text-align: left !important;
    }

    #restoreForm dl > dt {
        padding-top: 0.15rem;
        justify-self: start;
    }

    #restoreForm blockquote.inline_help {
        clear: both;
        max-width: 100%;
        margin: 0 0 0.75rem 0;
        overflow: hidden;
    }

    #restoreForm .restore-form__destination-note {
        margin: 0 0 0.65rem 0;
        line-height: 1.45;
    }

    #restoreForm .restore-form__actions {
        clear: both;
        margin: 0.75rem 0 1.5rem 0;
        padding-left: 54%;
        box-sizing: border-box;
    }

    #restoreForm .restore-form__actions button {
        width: auto !important;
        min-width: 6.5rem;
        max-width: 100%;
        display: inline-block !important;
        float: none !important;
    }

    #restoreForm #restoreBackupList {
        min-width: min(100%, 28rem);
        max-width: 100%;
    }

    #restoreForm .restore-form__checkbox-list {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        max-width: 100%;
    }

    #restoreForm .restore-form__checkbox-list label {
        display: flex;
        align-items: flex-start;
        gap: 0.4rem;
        margin: 0;
        font-weight: normal;
        text-align: left !important;
        cursor: pointer;
        word-break: break-word;
    }

    #restoreForm .restore-form__checkbox-list label input {
        flex-shrink: 0;
        margin-top: 0.2rem;
    }

    #restoreForm .restore-form__checkbox-row {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
    }

    #restoreForm .restore-form__checkbox-row input {
        margin: 0;
    }

    @media (max-width: 48rem) {
        #restoreForm dl {
            grid-template-columns: 1fr;
        }

        #restoreForm dl > dt {
            margin-bottom: 0.15rem;
        }

        #restoreForm .restore-form__actions {
            padding-left: 0;
        }
    }
</style>

<div class="title"><span class="left"><i class="fa fa-rotate-left title"></i>Restore</span></div>
<p>On this page, you are able to restore a previous made backup.</p>
<p>The restore process is able to:</p>
<ul>
    <li>Restore Container data</li>
    <li>Restore container template xml</li>
    <li>Restore extra files</li>
    <li>Restore backup configuration</li>
</ul>
<br/>
<p>The restore process <b>is NOT able to</b>:</p>
<ul>
    <li>Create your docker containers</li>
    <li>Take care of stopping containers prior restore
        <ul>
            <li>Please stop all maybe affected containers yourself prior to the restore!</li>
        </ul>
    </li>
</ul>

<form id="restoreForm">

    <div class="title"><span class="left"><i class="fa fa-folder title"></i>Step 1: Select source</span></div>
    <dl>
        <dt><b>Backup source:</b></dt>
        <dd><input type='text' required class='ftAttach' id="restoreSource" name="restoreSource"
                   value="<?= empty($abSettings->destination) ? '' : $abSettings->destination ?>"
                   data-pickfilter="HIDE_FILES_FILTER" data-pickfolders="true">
        </dd>
    </dl>

    <blockquote class='inline_help'>
        <p>The folder which contains <code>ab_xxx</code> folders.</p>
    </blockquote>

    <dl>
        <dt><b>Backup destination:</b></dt>
        <dd>
            <p class="restore-form__destination-note">The <b>default</b> destination will be the same as it were during backup. If the
                destination does not exist, it will be created. Any existing data will be overwritten!</p>
            <p class="restore-form__destination-note"><b>If you want to force a custom destination</b>, enter it below. The archive will be extracted
                there. <b>THIS IS ONLY APPLICABLE TO ARCHIVES!</b></p>
            <input type='text' class='ftAttach' id="customRestoreDestination" name="customRestoreDestination"
                   placeholder="Force custom destination"
                   data-pickfilter="HIDE_FILES_FILTER" data-pickfolders="true">
        </dd>
    </dl>

    <div class="restore-form__actions">
        <button type="button" onclick="checkRestoreSource(); return false;">Next</button>
    </div>

    <div id="restoreBackupDiv" style="display: none">
        <div class="title"><span class="left"><i class="fa fa-folder title"></i>Step 2: Select backup</span></div>
        <dl>
            <dt><b>Select backup:</b></dt>
            <dd><select required id="restoreBackupList" name="restoreBackupList"></select></dd>
        </dl>
        <div class="restore-form__actions">
            <button type="button" onclick="checkRestoreItem(); return false;">Next</button>
        </div>
    </div>

    <div id="restoreItemsDiv" style="display: none">
        <div class="title"><span class="left"><i class="fa fa-folder title"></i>Step 3: Select items</span></div>
        <p><b>Note:</b> If one item is not selectable, the chosen backup does not contain needed data.</p>

        <dl>
            <dt><b>Restore backup config?</b></dt>
            <dd><label class="restore-form__checkbox-row"><input type="checkbox" id="restoreItemConfig" name="restoreItem[config]"> Yes</label></dd>

            <dt><b>Restore extra files?</b></dt>
            <dd><label class="restore-form__checkbox-row"><input type="checkbox" id="restoreItemExtraFiles" name="restoreItem[extraFiles]"> Yes</label></dd>

            <dt><b>Restore VM meta?</b></dt>
            <dd><label class="restore-form__checkbox-row"><input type="checkbox" id="restoreItemVmMeta" name="restoreItem[vmMeta]"> Yes</label></dd>

            <dt><b>Restore templates?</b></dt>
            <dd><div class="restore-form__checkbox-list" id="restoreTemplatesDD"></div></dd>

            <dt><b>Restore containers?</b></dt>
            <dd><div class="restore-form__checkbox-list" id="restoreContainersDD"></div></dd>
        </dl>

        <div class="restore-form__actions">
            <button type="button" onclick="startRestore(); return false;">Do it!</button>
        </div>
    </div>

</form>

<script>
    function checkRestoreSource() {
        $('#restoreBackupDiv, #restoreItemsDiv').hide();
        $.ajax(url, {
            data: {action: 'checkRestoreSource', src: $('#restoreSource').val()}
        }).done(function (data) {
            if (data.result) {
                $('#restoreBackupList').html('');
                $('#restoreBackupDiv').show();
                $.each(data.result, function (i) {
                    var name = data.result[i]['name'];
                    $('#restoreBackupList').append('<option value="' + data.result[i]['path'] + '">' + name + '</option>');
                });
            } else {
                $('#restoreBackupDiv').hide();
                swal({
                    title: "Invalid source",
                    text: "The selected source seems invalid.",
                    type: "error",
                    confirmButtonText: "Ok"
                });
            }
        });
    }


    function checkRestoreItem() {
        $.ajax(url, {
            data: {action: 'checkRestoreItem', item: $('#restoreBackupList option:selected').val()}
        }).done(function (data) {
            if (data.result) {

                $('#restoreTemplatesDD, #restoreContainersDD').html('None available :(');

                $('#restoreItemsDiv').show();
                if (!data.result.configFile) {
                    $('#restoreItemConfig').prop('disabled', true);
                    $('#restoreItemConfig').prop('checked', false);
                } else {
                    $('#restoreItemConfig').prop('disabled', false);
                    $('#restoreItemConfig').prop('checked', false);
                }

                if (!data.result.extraFiles) {
                    $('#restoreItemExtraFiles').prop('disabled', true);
                    $('#restoreItemExtraFiles').prop('checked', false);
                } else {
                    $('#restoreItemExtraFiles').prop('disabled', false);
                    $('#restoreItemExtraFiles').prop('checked', false);
                }

                if (!data.result.vmMeta) {
                    $('#restoreItemVmMeta').prop('disabled', true);
                    $('#restoreItemVmMeta').prop('checked', false);
                } else {
                    $('#restoreItemVmMeta').prop('disabled', false);
                    $('#restoreItemVmMeta').prop('checked', false);
                }

                if (data.result.templateFiles) {
                    $('#restoreTemplatesDD').html('');
                    $.each(data.result.templateFiles, function (i) {
                        var file = data.result.templateFiles[i];
                        $('#restoreTemplatesDD').append(
                            '<label><input type="checkbox" name="restoreItem[templates][' + file + ']" /> <span>' + file + '</span></label>'
                        );
                    });
                }

                if (data.result.containers) {
                    $('#restoreContainersDD').html('');
                    $.each(data.result.containers, function (i) {
                        var container = data.result.containers[i];
                        $('#restoreContainersDD').append(
                            '<label><input type="checkbox" name="restoreItem[containers][' + container + ']" /> <span>' + container + '</span></label>'
                        );
                    });
                }

            } else {
                $('#restoreItemsDiv').hide();
                swal({
                    title: "Invalid backup",
                    text: "The selected backup seems invalid.",
                    type: "error",
                    confirmButtonText: "Ok"
                });
            }
        });
    }

    function startRestore() {
        $.ajax(url, {
            data: $('#restoreForm').serialize() + '&action=startRestore'
        }).always(function () {
            $('#tab3').click();
        });
    }
</script>
