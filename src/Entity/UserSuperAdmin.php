<?php

namespace App\Entity;

use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserSuperAdmin
 *
 * @ORM\Table(name="user_super_admin")
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
 class UserSuperAdmin extends User {

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Platform", inversedBy="superAdmins", cascade={"persist"})
     * @Serializer\Expose
     */
    private $platforms;

    public function setDefaultRoles() {

        $this->addRole(User::ROLE_SUPER_ADMIN);

        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->platforms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add platform.
     *
     * @param \App\Entity\Platform $platform
     *
     * @return UserSuperAdmin
     */
    public function addPlatform(\App\Entity\Platform $platform)
    {
        $this->platforms[] = $platform;
        
        return $this;
    }

    /**
     * Remove platform.
     *
     * @param \App\Entity\Platform $platform
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePlatform(\App\Entity\Platform $platform)
    {
        return $this->platforms->removeElement($platform);
    }

    /**
     * Get platforms.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlatforms()
    {
        return $this->platforms;
    }
}
