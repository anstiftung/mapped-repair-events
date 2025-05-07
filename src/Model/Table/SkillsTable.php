<?php
declare(strict_types=1);
namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use App\Model\Traits\ApproveMultipleTrait;

class SkillsTable extends Table
{

    use ApproveMultipleTrait;

    public array $allowedBasicHtmlFields = [];
    public string $name_de = 'Kenntnis';

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsToMany('Users', [
            'through' => 'UsersSkills',
            'foreignKey' => 'skill_id',
            'targetForeignKey' => 'user_uid',
            'sort' => [
                'Users.nick' => 'ASC',
            ]
        ]);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        return $validator;
    }

    /**
     * @param array<int|string>|string|null $associatedSkillIds
     */
    public function getNewSkillsFromRequest(array|string|null $associatedSkillIds): array
    {
        if (!is_array($associatedSkillIds)) {
            return [];
        }
        $skills = array_filter($associatedSkillIds, function($value): bool {
            return !is_numeric($value);
        });
        return $skills;
    }

    /**
     * @param array<int|string>|string|null $associatedSkillIds
     */
    public function getExistingSkillsFromRequest(array|string|null $associatedSkillIds): array
    {
        if (!is_array($associatedSkillIds)) {
            return [];
        }
        $skills = array_filter($associatedSkillIds, function($value): bool {
            return is_numeric($value);
        });
        return $skills;
    }

    /**
     * @param array<int|string> $newSkills
     */   
    public function addSkills(array $newSkills, bool $isAdmin, int $userUid): array
    {

        $skillsToAdd = [];
        foreach($newSkills as $skill) {
            $preparedSkill = strip_tags((string) $skill);
            $skillsToAdd[] = $this->newEntity([
                'name' => $preparedSkill,
                'status' => $isAdmin ? APP_ON : APP_OFF,
                'owner' => $userUid,
            ]);
        }

        $addedSkillIds = [];
        if (!empty($skillsToAdd)) {
            $addedSkills = $this->saveMany($skillsToAdd);
            foreach($addedSkills as $addedSkill) {
                $addedSkillIds[] = $addedSkill->id;
            }
        }

        return $addedSkillIds;

    }

    /**
     * @return array<int, string>
     */
    public function getForDropdownIncludingCategories(): array
    {
        $skillsForDropdown = $this->getForDropdown(false);
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $categoriesForDropdown = $categoriesTable->getMainCategoriesForFrontend();
        $preparedCategoriesForDropdown = [];
        foreach($categoriesForDropdown as $c) {
            $slugifiedCategoryName = StringComponent::slugify($c->name);
            $preparedCategoriesForDropdown[$slugifiedCategoryName] = $c->name;
        }
        $skillsForDropdown = $preparedCategoriesForDropdown + $skillsForDropdown;
        asort($skillsForDropdown);
        return $skillsForDropdown;
    }

    /**
     * @return array<int, string>
     */
    public function getForDropdown(bool $includeInactive): array
    {

        $conditions = [
            'Skills.status' => APP_ON,
        ];
        if ($includeInactive) {
            $conditions = [
                'Skills.status >= ' => APP_OFF,
            ];
        }
        $skills = $this->find('all',
        conditions: $conditions,
        fields: [
            'Skills.id',
            'Skills.name',
        ],
        order: [
            'LOWER(Skills.name)' => 'ASC',
        ]);

        $preparedSkills = [];
        foreach($skills as $skill) {
            $preparedSkills[$skill->id] = $skill->name;
        }

        return $preparedSkills;
    }

}

?>