<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use App\Model\Entity\ApiToken;
use App\Model\Table\AppTable;

/**
 * @extends AppTable<\App\Model\Entity\ApiToken>
 */
class ApiTokensTable extends AppTable
{
    public string $name_de = 'API Token';

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('api_tokens');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name', 'Bitte geben Sie einen Namen ein.');

        $validator
            ->scalar('token')
            ->maxLength('token', 64)
            ->requirePresence('token', 'create')
            ->notEmptyString('token');

        $validator->notEmptyString('allowed_search_terms');

        $validator
            ->dateTime('last_used')
            ->allowEmptyDateTime('last_used');

        $validator
            ->dateTime('expires_at')
            ->allowEmptyDateTime('expires_at');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['token']), [
            'errorField' => 'token',
            'message' => 'Dieses Token existiert bereits.',
        ]);

        return $rules;
    }

    /**
     * Find a valid token by its value
     */
    public function findByToken(string $token): ?ApiToken
    {
        return $this->find()
            ->where([
                'token' => $token,
                'is_active' => true,
            ])
            ->first();
    }

    /**
     * Update last_used timestamp for a token
     */
    public function updateLastUsed(int $id): bool
    {
        return $this->updateAll(
            ['last_used' => new \DateTime()],
            ['id' => $id],
        ) > 0;
    }
}
