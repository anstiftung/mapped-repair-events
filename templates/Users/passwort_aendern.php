<?php
declare(strict_types=1);
    echo $this->element('highlightNavi' ,['main' => 'Passwort ändern']);
?>
<div id="passwort-aendern">

    <?php echo $this->element('heading', ['first' => 'Passwort ändern']);

    echo $this->Form->create($user, [
        'id' => 'UserChangePasswordForm',
        'novalidate' => 'novalidate',
    ]);

        $this->Form->create($user);
        echo $this->Form->control('Users.password', ['label' => 'Altes Passwort*']);
        echo $this->Form->control('Users.password_new_1', ['label' => 'Neues Passwort*', 'type' => 'password']);
        echo $this->Form->control('Users.password_new_2', ['label' => 'Neues Passwort*', 'type' => 'password']);
    ?>

    <div class="right">
        <?php
            echo $this->Form->button('Passwort ändern', [
                'type' => 'submit',
                'class' => 'rounded',
            ]);
        ?>
    </div>

    <?php
        echo $this->Form->end();
    ?>

</div>