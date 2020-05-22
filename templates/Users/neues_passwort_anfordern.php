<div id="neues-passwort-anfordern">

  <?php echo $this->element('heading', ['first' => 'Neues Passwort anfordern']); ?>

    <form novalidate id="NeuesPasswortAnfordernForm" action="<?php echo $this->Html->urlNeuesPasswortAnfordern();?>" method="post">

        <?php
        $this->Form->create($user);
        echo $this->Form->control('Users.email', ['label' => 'E-Mail']);
        echo '<div class="sc"></div>';
        ?>

            <div class="right">
                <button type="submit" class="rounded">
                    Senden
                </button>
            </div>

        </form>

</div>