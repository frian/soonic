<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TableId
 *
 * @ORM\Table(name="table_id")
 * @ORM\Entity
 */
class TableId
{
    /**
     * @var integer
     *
     * @ORM\Column(name="max_id", type="integer", nullable=false)
     */
    private $maxId;

    /**
     * @var string
     *
     * @ORM\Column(name="table_name", type="string", length=128)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $tableName;


}

