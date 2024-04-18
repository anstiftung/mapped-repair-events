<div id="login-box">

<?php

  if (!empty($loggedUser)) {

      echo '<div class="top">';
      echo '<div class="greeting">'.__('Welcome&nbsp;').' ' . $loggedUser->nick . '</div>';
      echo '<div class="links">';

      echo $this->Html->link(__('My functions'), $this->Html->urlUserHome(),  [ 'id' => 'function-link'  ]). '<span class="divider"></span>'. $this->Html->link(__('Logout') ,$this->Html->urlLogout() ,['id' => 'abmelden-link']);
      echo '</div>';
      echo '</div>';

      echo '<div class="bottom">';
          if ($this->plugin == 'Admin') {
            echo $this->Html->link(__('To frontend'), '/');
          }
          if (($loggedUser->isAdmin() ) && $this->plugin == '') {
            echo $this->Html->link(__('To backend'), '/admin/pages/index');
          }


      echo '</div>';

 } else {

    ?>

    <button class="rounded" id="anmelden-link"><?php echo __('Loginbox:Login');?></button>
    <div id="login-box-form">
        <a href="<?php echo $this->Html->urlNeuesPasswortAnfordern();?>"><?php echo __('Loginbox:Forgot Pass?');?></a>
        <div class="sc" style="margin-bottom:12px;"></div>
        <?php
            $action = '/users/login';
            if (!empty($this->request->getQuery('login'))) {
                $action .= '/?login='.$this->request->getQuery('login');
            }

            echo $this->Form->create(@$loggedUser, ['url' => $action]);
            echo $this->Form->control('email', ['label' =>  '', 'value' => '', 'id' => 'rep-email', 'placeholder' => __('Loginbox:Email')]);
            echo $this->Form->control('password', ['label' => '', 'value' => '', 'id' => 'rep-password', 'placeholder' => __('Loginbox:Pass')]);
            echo '<div class="remember-me-wrapper">';
              echo $this->Form->control('remember_me', [
                  'type' => 'checkbox',
                  'label' => 'Angemeldet bleiben',
              ]);
            echo '</div>';
        ?>

<div class="sc"></div>

<a id="registrieren-link" href="<?php echo $this->Html->urlRegister();?>"><?php echo __('Loginbox:Register');?></a>
<button style="float:right;margin-top:10px;" type="submit" class="rounded" ><?php echo __('Loginbox:LoginButton');?></button>

<?php echo $this->Form->end(); ?>
</div>

<?php } ?>

</div>
