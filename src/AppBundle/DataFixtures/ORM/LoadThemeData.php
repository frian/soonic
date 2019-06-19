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
use AppBundle\Entity\Theme;

/**
 * User fixture
 *
 * @author André Friedli <a@frian.org>
 */
class LoadThemeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {

    	$themes = array(
    		0 => array(
    			'name' => 'default-dark',
    		),
            1 => array(
    			'name' => 'default-clear',
    		),
    	);

    	/**
    	 * Add themes
    	 */
    	foreach ( $themes as $index => $themeData ) {

	    	// create theme
	        $theme = new Theme();
	        $theme->setName($themeData['name']);

	        // add reference for further fixtures
	        $this->addReference('theme'.$index, $theme);

	    	$manager->persist($theme);
	    	$manager->flush();
    	}
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
    	return 2; // the order in which fixtures will be loaded
    }
}
