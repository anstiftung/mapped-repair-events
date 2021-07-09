<div id="neues-passwort-anfordern">

  <?php echo $this->element('heading', ['first' => 'Neues Passwort anfordern']); ?>

    <?php
        echo $this->Form->create($user, [
            'id' => 'NeuesPasswortAnfordernForm',
            'novalidate' => 'novalidate',
        ]);
        echo $this->Form->control('Users.email', ['label' => 'E-Mail']);
        echo '<div class="sc"></div>';
    ?>

        <div class="right">
            <?php
                echo $this->Form->button('Senden', [
                    'type' => 'submit',
                    'class' => 'rounded',
                ]);
            ?>
        </div>

    <?php
        echo $this->Form->end();
    ?>

</div>