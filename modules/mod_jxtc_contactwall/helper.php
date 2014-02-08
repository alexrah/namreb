<?php
/*
	JoomlaXTC Contact Wall

	version 1.5.0

	Copyright (C) 2008-2012 Monev Software LLC.	All Rights Reserved.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	THIS LICENSE IS NOT EXTENSIVE TO ACCOMPANYING FILES UNLESS NOTED.

	See COPYRIGHT.php for more information.
	See LICENSE.php for more information.

	Monev Software LLC
	www.joomlaxtc.com
*/

defined( '_JEXEC' ) or die;

jimport( 'joomla.html.parameter' );


class mod_jxtc_contactwallHelper
{
    /**
     * Retrieves the hello message
     *
     * @access public
     */
    public static function getData( $catid, $varaux, $sortfield, $sortorder, $linked, $image )
    {
        $contentconfig = JComponentHelper::getParams( 'com_contact' );

        $user = JFactory::getUser();
        $date = JFactory::getDate();
        $db = JFactory::getDBO();
        $now = $date->toSQL();
        $nullDate = $db->getNullDate();
        
        $accesslevel = !$contentconfig->get('show_noauth');

        $aid = $user->get('aid', 0);

        $query = "SELECT c.id AS catid, c.title, c.description, cd.id, cd.name AS contactname, cd.alias AS contactalias,
            CASE WHEN CHAR_LENGTH(cd.alias) THEN CONCAT_WS(':', cd.id, cd.alias) ELSE cd.id END as slug,
            CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(':', c.id, c.alias) ELSE c.id END as catslug,
            cd.con_position, cd.address, cd.suburb, cd.state, cd.country, cd.postcode, cd.telephone,
            cd.fax, cd.misc, cd.image, cd.email_to, cd.mobile, cd.webpage, u.name as username, u.username as useralias FROM #__categories AS c,
            #__contact_details AS cd LEFT JOIN #__users AS u ON u.id = cd.user_id  WHERE (
            c.extension= 'com_contact_details' or c.extension= 'com_contact' 
            ) AND c.published = 1
            AND cd.published = 1 AND c.id = cd.catid";

        if ($accesslevel) {
            $groups	= implode(',', $user->getAuthorisedViewLevels());
            $query .= ' AND c.access IN ('.$groups.')';
        }

        if ($catid) {
            if(is_array($catid)){
                if($catid[0] != 0)
                    $query .= " AND c.id IN(".implode(',', $catid).")";
            }
            else{
                $query .= ' AND (c.id='.$catid.')';
            }
        }

        if ($linked){
            $query .= ' AND cd.user_id > 0';
        }

        if ($image){
            $query .= " AND cd.image != ''";
        }

        $aux = ($sortorder == '0') ? ' ASC ' : ' DESC ';

        switch ($sortfield) {
            case '0': // creation
                $query .= ' ORDER BY c.level'.$aux;
            break;
            case '1': // modified
                $query .= ' ORDER BY c.title'.$aux;
            break;
            case '2': // modified
                $query .= ' ORDER BY cd.name'.$aux;
            break;
            case '3': // modified
                $query .= ' ORDER BY cd.ordering'.$aux;
            break;
            case '4': // modified
                $query .= ' ORDER BY cd.country'.$aux;
            break;
            case '5': // modified
                $query .= ' ORDER BY cd.email_to'.$aux;
            break;
            case '6':
                $query .= ' ORDER BY RAND()';
            break;
        }

        $db->setQuery($query, 0, $varaux);
        $items = $db->loadObjectList();

        return $items;
    }
}
?>