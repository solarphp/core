<?php

/**
* 
* Locale file.  Returns the strings for a specific language.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Locale
* 
* @author Jean-Eric Laurent <jel@jelaurent.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

return array(
	
	// formatting codes and information
	'FORMAT_LANGUAGE'    => 'Français',
	'FORMAT_COUNTRY'     => 'France',
	'FORMAT_CURRENCY'    => 'EUR €%s', // printf()
	'FORMAT_DATE'        => '%j %m %Y', // strftime(): 19 Mar 2005
	'FORMAT_TIME'        => '%r', // strftime: 24-hour 
	
	// operation actions
	'OP_SAVE'            => 'Sauvegarder',
	'OP_PREVIEW'         => 'Previsualisation',
	'OP_CANCEL'          => 'Annuler',
	'OP_DELETE'          => 'Effacer',
	'OP_RESET'           => 'Réinitialiser',
	'OP_NEXT'            => 'Prochain',
	'OP_PREVIOUS'        => 'Précédent',
	'OP_SEARCH'          => 'Chercher',
	'OP_GO'              => 'Action!',
	
	// error messages
	'ERR_FORM'           => 'Merci de corriger les erreurs affichées.',
	'ERR_FILE_FIND'      => 'Impossible de trouver le fichier.',
	'ERR_FILE_OPEN'      => 'Impossible d\'ouvrir le fichier.',
	'ERR_FILE_READ'      => 'Impossible de lire le fichier.',
	'ERR_EXTENSION'      => 'Extension non chargée.',
	'ERR_CONNECT'        => 'Connection invalide.',
	'ERR_INVALID'        => 'Donnée invalide.',
	
	// validation messages
	'VALID_ALPHA'        => 'Veuillez utiliser esclusivement les lettres A-Z.',
	'VALID_ALNUM'        => 'Veuillez utiliser esclusivement les lettres (A-Z) et les nombres (0-9)',
	'VALID_BLANK'        => 'Cette valeur n\'est pas autorisée.',
	'VALID_EMAIL'        => 'Veuillez entrer une adresse email valide.',
	'VALID_INKEYS'       => 'Veuillez choisir une valeur différente.',
	'VALID_INLIST'       => 'Veuillez choisir une valeur différente.',
	'VALID_INSCOPE'      => 'Cette valeur n\'appartient pas à la cible définie.',
	'VALID_INTEGER'      => 'Veuillez exclusivement utiliser des nombres entiers.',
	'VALID_ISODATE'      => 'Veuillez entrer une date au format "yyyy-mm-dd".',
	'VALID_ISODATETIME'  => 'Veuillez entrer une date-temps au format "yyyy-mm-ddThh:mm:ss".',
	'VALID_ISOTIME'      => 'Veuillez entrer un temps au format "hh:mm:ss".',
	'VALID_MAX'          => 'Veuillez entrer une valeur plus petite.',
	'VALID_MAXLENGTH'    => 'Veuillez entrer un texte moins long.',
	'VALID_MIN'          => 'Veuillez entrer une plus grande valeur.',
	'VALID_MINLENGTH'    => 'Veuillez entrer un texte plus long.',
	'VALID_NONZERO'      => 'Cette valeur ne peut pas être zéro.',
	'VALID_NOTBLANK'     => 'Cette valeur doit être laissée en blanc.',
	'VALID_URI'          => 'Veuiilez entrer une adresse web valide.',
	
	// success messages
	'OK_SAVED'           => 'Sauvegardé.',
	
	// generic text
	'TEXT_LOGIN'         => 'Sign In',
	'TEXT_LOGOUT'        => 'Sign Out',
	'TEXT_AUTH_USERNAME' => 'Identifié comme',
	
	// generic labels
	'LABEL_USERNAME'     => 'Identifiant',
	'LABEL_PASSWORD'     => 'Mot de passe',
);
?>