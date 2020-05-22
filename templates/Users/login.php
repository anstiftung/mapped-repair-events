<div id="ViewsWrapper">


<?php
echo $this->element('highlightNavi' ,array('main' => ''));
echo $this->Html->Tag('h1', __('LoginPage'));


echo __('LoginPagetext');

?>
<br><br>
<a href="<?php echo $this->Html->urlNeuesPasswortAnfordern();?>">Password vergessen?</a>
<div class="sc" style="margin-bottom:14px;"></div>


<?php
    echo $this->Form->create(null, [

    ]);
?>

<?php echo $this->Form->control('email',array('label' => __('Login: Your Email').' *')); ?>


<div class="sc" style="margin-bottom:5px;"></div>


<?php  echo $this->Form->control('password', array('label' => __('Login: Your Password').' *')); ?>

<div class="sc" style="margin-bottom:7px;"></div>

<a id="registrieren-link" href="<?php echo $this->Html->urlRegister();?>">Registrieren</a>
<button type="submit" class="rounded" >Los geht's</button>

<?php echo $this->Form->end(); ?>
</div>