<?php
    echo $this->element('highlightNavi' ,['main' => 'Passwort ändern']);
?>
<div id="passwort-aendern">

    <?php echo $this->element('heading', ['first' => 'Passwort ändern']); ?>

        <form novalidate id="UserChangePasswordForm" action="<?php echo $this->Html->urlPasswortAendern();?>" method="post">

        <?php
            $this->Form->create($user);
            echo $this->Form->control('Users.password', ['label' => 'Altes Passwort*']);
            echo $this->Form->control('Users.password_new_1', ['label' => 'Neues Passwort*', 'type' => 'password']);
            echo $this->Form->control('Users.password_new_2', ['label' => 'Neues Passwort*', 'type' => 'password']);
        ?>

            <div class="right">
                <button type="submit" class="rounded">
                    Password ändern
                </button>
            </div>

        </form>

</div>