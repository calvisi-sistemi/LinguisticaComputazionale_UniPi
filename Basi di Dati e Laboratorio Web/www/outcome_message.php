<!-- Visualizzazione di messaggi di successo ed errore-->
<div class="outcome_message">

    <?php if (there_is_an_error()): ?>
        
        <p class="error_message_text">
            <?php echo get_error(); ?>
        </p>

    <?php elseif (isset($_SESSION['success'])): ?>
        
        <p class="sucess_message_text">
            <?php echo $_SESSION['success']; ?>
        </p>
        
        <?php unset($_SESSION['success']) ?>
    
    <?php endif; ?>

</div>