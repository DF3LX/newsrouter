<?php

namespace DARCNews\Core;

/**
 * In der Anwendung verwendete Fehlercodes
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
class ErrorCodes
{
    /** @var int  OK Kein Fehler */
    public const OK = 0;
 
    /** @var int  ModulNotFound Modul nicht gefunden */
    public const ModulNotFound = -1;
 
    /** @var int  AlreadyExists Ein Objekt mit diesem Namen existiert bereits */
    public const AlreadyExists = -2;
 
    /** @var int  ParameterNotFound Parameter wurde nicht gefunden */
    public const ParameterNotFound = -3;
 
    /** @var int  ValueNotFound Angegebener Wert konnte nicht aufgelöst werden */
    public const ValueNotFound = -4;

    /** @var int  Operation_Failed Irgendwas geht nicht */
    public const Operation_Failed = -5;
 
    /** @var int  NotEnoughArguments Nicht genügend Argumente oder Parameter-Angaben */
    public const NotEnoughArguments = -6;
 
    /** @var int  MultiValueNoOperator MultiValue-Befehl ohne Operator */
    public const MultiValueNoOperator = -7;
 
    /** @var int  NotReady Modul ist nicht bereit */
    public const NotReady = -8;
 
    /** @var int  InvalidValue Ungültiver Wert */
    public const InvalidValue = -9;

}