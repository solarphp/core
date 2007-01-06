<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 * @author Jean-Eric Laurent <jel@jelaurent.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
    'PROCESS_SAVE'       => 'Sauvegarder',
    'PROCESS_PREVIEW'    => 'Previsualisation',
    'PROCESS_CANCEL'     => 'Annuler',
    'PROCESS_DELETE'     => 'Effacer',
    'PROCESS_RESET'      => 'Réinitialiser',
    'PROCESS_NEXT'       => 'Prochain',
    'PROCESS_PREVIOUS'   => 'Précédent',
    'PROCESS_SEARCH'     => 'Chercher',
    'PROCESS_GO'         => 'Action!',
    'PROCESS_LOGIN'      => 'Sign In',
    'PROCESS_LOGOUT'     => 'Sign Out',
    
    // error messages
    'ERR_FILE_NOT_FOUND'       => 'Impossible de trouver le fichier.',
    'ERR_FILE_NOT_READABLE'    => 'Impossible de lire le fichier.',
    'ERR_EXTENSION_NOT_LOADED' => 'Extension non chargée.',
    'ERR_CONNECTION_FAILED'    => 'Connection invalide.',
    'ERR_INVALID'              => 'Donnée invalide.',
    
    // validation messages
    'VALID_ALPHA'        => 'Veuillez utiliser esclusivement les lettres A-Z.',
    'VALID_ALNUM'        => 'Veuillez utiliser esclusivement les lettres (A-Z) et les nombres (0-9)',
    'VALID_BLANK'        => 'Cette valeur n\'est pas autorisée.',
    'VALID_EMAIL'        => 'Veuillez entrer une adresse email valide.',
    'VALID_INKEYS'       => 'Veuillez choisir une valeur différente.',
    'VALID_INLIST'       => 'Veuillez choisir une valeur différente.',
    'VALID_SCOPE'      => 'Cette valeur n\'appartient pas à la cible définie.',
    'VALID_INTEGER'      => 'Veuillez exclusivement utiliser des nombres entiers.',
    'VALID_ISODATE'      => 'Veuillez entrer une date au format "yyyy-mm-dd".',
    'VALID_ISOTIMESTAMP' => 'Veuillez entrer une date-temps au format "yyyy-mm-ddThh:mm:ss".',
    'VALID_ISOTIME'      => 'Veuillez entrer un temps au format "hh:mm:ss".',
    'VALID_MAX'          => 'Veuillez entrer une valeur plus petite.',
    'VALID_MAXLENGTH'    => 'Veuillez entrer un texte moins long.',
    'VALID_MIN'          => 'Veuillez entrer une plus grande valeur.',
    'VALID_MINLENGTH'    => 'Veuillez entrer un texte plus long.',
    'VALID_NOTZERO'      => 'Cette valeur ne peut pas être zéro.',
    'VALID_NOTBLANK'     => 'Cette valeur doit être laissée en blanc.',
    'VALID_URI'          => 'Veuiilez entrer une adresse web valide.',
    
    // success/failure messages
    'SUCCESS_SAVED'           => 'Sauvegardé.',
    'FAILURE_FORM'                 => 'Merci de corriger les erreurs affichées.',
    
    // generic text
    'TEXT_AUTH_USERNAME' => 'Identifié comme',
    
    // generic labels
    'LABEL_HANDLE'     => 'Identifiant',
    'LABEL_PASSWD'     => 'Mot de passe',
);
