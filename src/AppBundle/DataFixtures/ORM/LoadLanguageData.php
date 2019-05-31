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
use AppBundle\Entity\Language;

/**
 * User fixture
 *
 * @author André Friedli <a@frian.org>
 */
class LoadLanguageData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

    	$languages = array(
    		0 => array(
    			'name' => 'english',
                'code' => 'en'
    		),
    		1 => array(
    			'name' => 'french',
                'code' => 'fr'
    		),
            2 => array(
    			'name' => 'italian',
                'code' => 'it'
    		),
    	);

    	/**
    	 * Add users
    	 */
    	foreach ( $languages as $index => $languageData ) {

	    	// create user
	        $language = new Language();
	        $language->setName($languageData['name']);
            $language->setCode($languageData['code']);

	        // add reference for further fixtures
	        $this->addReference('language'.$index, $language);

	    	$manager->persist($language);
	    	$manager->flush();
    	}

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
    	return 1; // the order in which fixtures will be loaded
    }
}
