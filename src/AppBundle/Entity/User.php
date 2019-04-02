<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
{
    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=256, nullable=false)
     */
    private $password;

    /**
     * @var integer
     *
     * @ORM\Column(name="bytes_streamed", type="bigint", nullable=false)
     */
    private $bytesStreamed = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="bytes_downloaded", type="bigint", nullable=false)
     */
    private $bytesDownloaded = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="bytes_uploaded", type="bigint", nullable=false)
     */
    private $bytesUploaded = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="ldap_authenticated", type="boolean", nullable=false)
     */
    private $ldapAuthenticated = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=256, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $username;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Role", inversedBy="username")
     * @ORM\JoinTable(name="user_role",
     *   joinColumns={
     *     @ORM\JoinColumn(name="username", referencedColumnName="username")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     *   }
     * )
     */
    private $role;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->role = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

