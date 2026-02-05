<?php

        echo 'Falsch eingereichte Verwendungsnachweise: ' . count($errorFundings) . '<br /><br />';
        foreach($errorFundings as $errorFunding) {
            echo 'FundingUID: ' . $errorFunding->uid . ' - ' . $errorFunding->workshop->name . '<br />';
        }
