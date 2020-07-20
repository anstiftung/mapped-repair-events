<?php
/**
 *   In order to use forum please install fluxbb forum manually
 *   see AppConfig.fluxBbForumEnabled
 *   folgende anpassungen wurden vorgenommen, damit eine zentrale user-verwaltung (und zwar die von cake) verwendet wird
 * - einträge in die flux-user-tabelle (flux_user) mittels updateUserTable
 * - bei jeder registrierung wird ein weiterer eintrag in diese tabelle erstellt
 * - login / logout anpassungen
 * - frontend-einbindung von fluxbb (login-box oben rechts)
 */
namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;

class FluxBbComponent extends AppComponent
{

    public function __construct(ComponentRegistry $collection, $settings = [])
    {
        parent::__construct($collection, $settings);

        $this->User = FactoryLocator::get('Table')->get('Users');
        $this->connection = ConnectionManager::get('default');

        // bei mehrmaligem require wird constante pun mehrfach definiert => notice
        require (WWW_ROOT . 'forum/config.php');
        $this->cookie_name = $cookie_name;
        $this->cookie_path = $cookie_path;
        $this->cookie_seed = $cookie_seed;
        $this->cookie_domain = $cookie_domain;
        $this->cookie_secure = $cookie_secure;
    }

    public function doServerDbAdaption()
    {
        $server = Configure::read('app.server' . ucfirst(Configure::read('app.environment')));
        $query = "UPDATE fluxbb_config SET conf_value = '" . $server . "/forum' WHERE conf_name = 'o_base_url';";
        $this->connection->execute($query);
    }

    public function changeUserData($user)
    {

        $fluxUser = $this->getFluxUser($user->uid);

        $query = "
            UPDATE fluxbb_forums f JOIN fluxbb_users u ON u.username = f.last_poster SET f.last_poster = :username WHERE u.id = :fluxUserId;
            UPDATE fluxbb_topics t JOIN fluxbb_users u ON u.username = t.last_poster SET t.last_poster = :username WHERE u.id = :fluxUserId;
            UPDATE fluxbb_posts SET poster = :username WHERE poster_id = :fluxUserId;
        ";
        $params = [
            'username' => $user->nick,
            'fluxUserId' => $fluxUser['FluxUsers']['id']
        ];
        $this->connection->execute($query, $params);

        $query = "UPDATE fluxbb_users SET username = :username, email = :email WHERE id = :fluxUserId;";
        $params = [
            'username' => $user->nick,
            'email' => $user->email,
            'fluxUserId' => $fluxUser['FluxUsers']['id']
        ];
        $this->connection->execute($query, $params);

    }

    public function changeUserGroup($user, $groups)
    {
        $fluxUser = $this->getFluxUser($user->uid);
        arsort($groups); // damit admin gewinnt, groups zuerst absteigend sortieren

        $groupId = 4; // fluxx bb group member
        foreach ($groups as $group) {
            // fluxbb unterstützt nur einfach-zuordnung. falls mehrfach-zuordnung, gewinnt admin
            if (in_array($group, [
                GROUPS_ADMIN
            ])) {
                $groupId = 1; // flux bb group admin
            }
        }

        $query = "UPDATE `fluxbb_users` SET `group_id` = '" . $groupId . "'
                WHERE `id` = '" . $fluxUser['FluxUsers']['id'] . "'";
        $this->connection->execute($query);
    }

    public function insert($userUid, $username, $email)
    {
        $fluxUser = $this->getFluxUser($userUid);
        if (! empty($fluxUser))
            return;

        $sql = "
            INSERT INTO `fluxbb_users` (
                `username`, `group_id`, `email`,
                `email_setting`, `timezone`, `dst`, `language`,
                `style`, `registered`, `registration_ip`, `last_visit`
            )
            VALUES(
                :username, '4', :email, '1', 0, 0, 'German', 'Air', :time, :remoteAddr, :time
            )";

        $params = [
            'username' => $username,
            'email' => $email,
            'time' => time(),
            'remoteAddr' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
        ];

        $this->connection->execute($sql, $params);
    }

    private function getFluxUser($userUid)
    {
        $fluxUser = $this->User->find('all', [
            'fields' => [
                'FluxUsers.id',
                'FluxUsers.password',
            ],
            'join' => [
                [
                    'type' => 'INNER',
                    'table' => 'fluxbb_users',
                    'alias' => 'FluxUsers',
                    'conditions' => 'FluxUsers.email = Users.email'
                ]
            ],
            'conditions' => [
                'Users.uid' => $userUid,
                'Users.status >= ' . APP_DELETED
            ]
        ])->first();

        return $fluxUser;
    }

    public function getExpire()
    {
        return time() + Configure::read('Session.timeout') * 60;
    }

    public function login($userUid)
    {

        // Remove this users guest entry from the online list
        $this->connection->execute("DELETE FROM `fluxbb_online` WHERE ident='" . $_SERVER['REMOTE_ADDR'] . "'");

        require_once ($_SERVER['DOCUMENT_ROOT'] . 'forum/include/functions.php'); // wegen forum_hmac() und forum_setcookie()

        $fluxUser = $this->getFluxUser($userUid);
        $expire = $this->getExpire();

        $cookieValue = $fluxUser['FluxUsers']['id'] . '|' . forum_hmac($fluxUser['FluxUsers']['password'], $this->cookie_seed . '_password_hash') . '|' . $expire . '|' . forum_hmac($fluxUser['FluxUsers']['id'] . '|' . $expire, $this->cookie_seed . '_cookie_hash');
        setcookie(
            $this->cookie_name,
            $cookieValue,
            $expire,
            $this->cookie_path,
            $this->cookie_domain,
            $this->cookie_secure,
            true
        );
    }

    public function logout($userUid)
    {
        $fluxUser = $this->getFluxUser($userUid);

        // Remove this users guest entry from the online list
        $this->connection->execute("DELETE FROM `fluxbb_online` WHERE user_id='" . $fluxUser['FluxUsers']['id'] . "'");

        setcookie($this->cookie_name, md5(uniqid(rand(), true)), time(), $this->cookie_path, $this->cookie_domain, $this->cookie_secure, true);
    }
}