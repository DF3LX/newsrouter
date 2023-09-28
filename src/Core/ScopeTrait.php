<?php

namespace DARCNews\Core;

/**
 * Namespace-abhängige Funktionen.
 * Ist ein Trait, weil ich die mehrmals brauche
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
trait ScopeTrait
{
    /**
     * Gibt den Namespace ohne Klassennnamen zurück
     * @return string   der Namespace ohne Klassenname
     */
    private function getNamespace() : string
    {
        // return Namespace mit abschließendem \
        return substr(static::class, 0, strrpos(static::class, '\\')+1);
    }

    /**
     * Ermittelt den Namespace-bestandteil direkt über der Klasse
     * @return string   der Sub-Namespace in dem die Klasse ist
     */
    private static function getScope() :string 
    {
        $nsArr = explode('\\', static::class);
        $scope = $nsArr[count($nsArr)-2];

        return $scope;
    }

    /**
     * Ermittelt nur den Typnamen ohne Namespace
     * @return string   Typnamen ohne Namespace
     */
    public static function getTypeName() : string
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }
}