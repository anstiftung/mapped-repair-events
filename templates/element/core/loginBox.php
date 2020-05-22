<div id="login-box">

<?php

  if ($appAuth->user()) {

      echo '<div class="top">';
      echo '<div class="greeting">'.__('Welcome&nbsp;').' '.$appAuth->getUserNick().'</div>';
      echo '<div class="links">';

      echo $this->Html->link(__('My functions'), $this->Html->urlUserHome(),  [ 'id' => 'function-link'  ]). '<span class="divider"></span>'. $this->Html->link(__('Logout') ,$this->Html->urlLogout() ,['id' => 'abmelden-link']);
      echo '</div>';
      echo '</div>';

      echo '<div class="bottom">';
          if ($this->plugin == 'Admin') {
            echo $this->Html->link(__('To frontend'), '/');
          }
          if (($appAuth->isAdmin() ) && $this->plugin == '') {
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

            echo $this->Form->create($loggedUser, ['url' => $action]);
            echo $this->Form->control('email', ['label' =>  __('Loginbox:Email'), 'value' => '']);
            echo $this->Form->control('password', ['label' => __('Loginbox:Pass'), 'value' => '']);
        ?>

<div class="sc"></div>

<a id="registrieren-link" href="<?php echo $this->Html->urlRegister();?>"><?php echo __('Loginbox:Register');?></a>
<button style="float:right;margin-top:10px;" type="submit" class="rounded" ><?php echo __('Loginbox:LoginButton');?></button>

</form>
</div>

<?php } ?>

</div>
