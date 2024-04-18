<div id="ViewsWrapper">


<?php
echo $this->element('highlightNavi' ,['main' => '']);
echo $this->Html->Tag('h1', __('LoginPage'));


echo __('LoginPagetext');

?>
<br><br>
<a href="<?php echo $this->Html->urlNeuesPasswortAnfordern();?>">Password vergessen?</a>
<div class="sc" style="margin-bottom:14px;"></div>

<?php echo $this->Form->create(null, []); ?>

<?php echo $this->Form->control('email', ['label' => __('Login: Your Email').' *']); ?>
<div class="sc" style="margin-bottom:5px;"></div>
<?php  echo $this->Form->control('password', ['label' => __('Login: Your Password').' *']); ?>

<?php
echo '<div class="remember-me-wrapper" style="float:right;">';
    echo $this->Form->control('remember_me', [
        'type' => 'checkbox',
        'label' => 'Angemeldet bleiben',
    ]);
echo '</div>';
?>
<div class="sc" style="margin-bottom:7px;"></div>

<a id="registrieren-link" href="<?php echo $this->Html->urlRegister();?>">Registrieren</a>
<button type="submit" class="rounded" >Los geht's</button>

<?php echo $this->Form->end(); ?>
</div>