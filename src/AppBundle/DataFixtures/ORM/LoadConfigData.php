<?php

/**
 * This file is part of the Sooni package.
 *
 * (c) TimeTM <https://github.com/soonic>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Config;

/**
 * User fixture
 *
 * @author André Friedli <a@frian.org>
 */
class LoadConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

    	$configData = array('language' => 'en');

    	/**
    	 * Add config
    	 */
    	// create config
        $config = new Config();
        $config->setLanguage($configData['language']);

        // add reference for further fixtures
        $this->addReference('config', $config);

    	$manager->persist($config);
    	$manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
    	return 2; // the order in which fixtures will be loaded
    }
}
