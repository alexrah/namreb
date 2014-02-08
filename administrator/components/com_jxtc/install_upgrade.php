<?php
/***********************************************************************************
************************************************************************************
***                                                                              ***
***   XTC Template Framework helper                                              ***
***                                                                              ***
***   Copyright (c) 2010-2012 Monev Software LLC,  All Rights Reserved           ***
***                                                                              ***
***   This program is free software; you can redistribute it and/or modify       ***
***   it under the terms of the GNU General Public License as published by       ***
***   the Free Software Foundation; either version 2 of the License, or          ***
***   (at your option) any later version.                                        ***
***                                                                              ***
***   This program is distributed in the hope that it will be useful,            ***
***   but WITHOUT ANY WARRANTY; without even the implied warranty of             ***
***   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              ***
***   GNU General Public License for more details.                               ***
***                                                                              ***
***   You should have received a copy of the GNU General Public License          ***
***   along with this program; if not, write to the Free Software                ***
***   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA   ***
***                                                                              ***
***   See COPYRIGHT.php for more information.                                    ***
***   See LICENSE.php for more information.                                      ***
***                                                                              ***
***   www.joomlaxtc.com                                                          ***
***                                                                              ***
************************************************************************************
***********************************************************************************/

defined('_JEXEC') or die;

// Component cleanup

jimport('joomla.filesystem.file');

if (JFile::exists(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jxtc'.DS.'install.xml')) {
	JFile::delete(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jxtc'.DS.'install.xml');
}

?>