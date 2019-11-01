<?php

namespace App\Entity;

use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserAdmin
 *
 * @ORM\Table(name="user_admin")
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class UserAdmin extends User {

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Platform", inversedBy="admins")
     * @ORM\JoinColumn(name="platform_id", referencedColumnName="id")
     * @Serializer\Expose
     * @Serializer\MaxDepth(3)
     */
    private $platform;

    public function setDefaultRoles() {

        $this->addRole(User::ROLE_ADMIN);

        return $this;
    }

    /**
     * Set platform.
     *
     * @param \App\Entity\Platform|null $platform
     *
     * @return UserAdmin
     */
    public function setPlatform(\App\Entity\Platform $platform = null) {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform.
     *
     * @return \App\Entity\Platform|null
     */
    public function getPlatform() {
        return $this->platform;
    }

}
