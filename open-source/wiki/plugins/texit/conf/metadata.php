<?php
/*
 * TeXit plugin, configuration settings
 * Copyright (C) 2013 Elie Roux <elie.roux@telecom-bretagne.eu>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 *
 */
$meta['ns_button'] = array('onoff'); // Show Download as PDF button
$meta['latex_mode'] = array('multichoice','_choices' => array('latex', 'pdflatex', 'lualatex', 'xelatex'));
$meta['latexmk_path'] = array('string');
$meta['use_prefix'] = array('onoff');
$meta['pre_prefix'] = array('string');
$meta['prefix_separator'] = array('string');
$meta['texitdir'] = array('string');
$meta['includestart'] = array('onoff');
$meta['recurse'] = array('multichoice','_choices' => array('off', 'on', 'chapter', 'appendix'));
$meta['recurse_file'] = array('onoff');
