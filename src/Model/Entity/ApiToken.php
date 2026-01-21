<?php
declare(strict_types=1);
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Security;

class ApiToken extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'token' => true,
        'allowed_search_terms' => true,
        'last_used' => true,
        'expires_at' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = [
        'token',
    ];

    /**
     * Generate a secure random token
     */
    public static function generateToken(): string
    {
        return bin2hex(Security::randomBytes(32));
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if token is valid (active and not expired)
     */
    public function isValid(): bool
    {
        return $this->status === 1 && !$this->isExpired();
    }

    /**
     * Check if a search term is allowed for this token
     */
    public function isSearchTermAllowed(string $searchTerm): bool
    {
        if (empty($this->allowed_search_terms)) {
            return false; // Empty search terms = no access
        }

        $allowedTerms = is_string($this->allowed_search_terms) 
            ? json_decode($this->allowed_search_terms, true) 
            : $this->allowed_search_terms;

        if (!is_array($allowedTerms) || empty($allowedTerms)) {
            return false; // Invalid or empty = no access
        }
        
        foreach ($allowedTerms as $allowedTerm) {
            if (mb_strtolower($allowedTerm) === mb_strtolower($searchTerm)) {
                return true;
            }
        }

        return false;
    }
}
