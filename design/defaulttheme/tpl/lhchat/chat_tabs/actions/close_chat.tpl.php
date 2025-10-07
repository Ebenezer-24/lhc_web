<?php
/*
    Versión final. Corregimos la URL del modal para que sea una ruta directa.
*/
?>
<?php if (isset($canEditChat) && $canEditChat == true && ($chat->user_id == erLhcoreClassUser::instance()->getUserID() || erLhcoreClassUser::instance()->hasAccessTo('lhchat','allowcloseremote'))) : ?>

    <button type="button" class="btn btn-xs text-muted fs14"
        onclick="lhc.revealModal({'url':'/formulario_cierre.php?chat_id=<?php echo $chat->id?>','height':450,'iframe':true})"
        title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Close chat')?>">
        <span class="material-icons fs14">close</span>
        <span class="close-text"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Close chat')?></span>
    </button>

<?php else : ?>

    <button type="button" class="btn btn-xs text-muted fs14"
        onclick="lhinst.removeDialogTab(<?php echo $chat->id?>,$('#tabs'),true)"
        title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Exit chat')?>">
        <span class="material-icons fs14">close</span>
        <span class="close-text"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('chat/adminchat','Exit chat')?></span>
    </button>

<?php endif; ?>
