<?php
    $id = $id ?? sha1(microtime() . rand(0, 9999));
    $disable_backdrop = isset($disable_backdrop) && is_bool($disable_backdrop) ? $disable_backdrop : true;
    $show_confirm_btn = isset($show_confirm_btn) && is_bool($show_confirm_btn) ? $show_confirm_btn : true;
    $show_cancel_btn = isset($show_cancel_btn) && is_bool($show_cancel_btn) ? $show_cancel_btn : true;
?>
<div class="modal fade" id="<?=$id?>" aria-labelledby="<?=$id?>Label" aria-hidden="true" <?=$disable_backdrop ? 'data-backdrop="static" data-keyboard="false"' : ''?>>
    <div class="modal-dialog <?=!empty($size) ? $size : ""?>" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?=$id?>Label"><?=$title ?? ""?></h5>
                <?php if($show_cancel_btn): ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <?php endif; ?>
            </div>
            <div class="modal-body"><?=$body?></div>
            <div class="modal-footer">
            <?php if($show_cancel_btn): ?>
                <button type="button" class="btn btn-secondary modal-btn-cancel" data-bs-dismiss="modal" data-action="modal-cancel"><?=__($cancel_button ?? "Chiudi")?></button>
            <?php endif; ?>
            <?php if($show_confirm_btn): ?>
                <button type="button" class="btn btn-primary modal-btn-confirm" data-action="modal-confirm"><?=__($confirm_button ?? "Conferma")?></button>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>