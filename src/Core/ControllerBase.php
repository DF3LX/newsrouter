<?php

namespace DARCNews\Core;

/**
 * Summary of ControllerBase
 * 
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */
abstract class ControllerBase
{
    use ScopeTrait;

    /**
     * @var ModuleBase[] $Modules    Array of configurable modules
     */
    private array $Modules = [];


    /**
     * The Singleton's instance is stored in a static field. This field is an
     * array, because we'll allow our Singleton to have subclasses. Each item in
     * this array will be an instance of a specific Singleton's subclass. You'll
     * see how this works in a moment.
     *
     * @var ControllerBase $instances
     */
    private static $instances = [];

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct()
    {
        global $pdo;
        // Suche alle konfigurierten Module
        $statement = $pdo->prepare("SELECT id, classname from " . static::getScope());
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_NUM);
        $statement->closeCursor();
        $statement = null;

        foreach ($result as list($id, $classname)) // ordne die 2 Spalten 2 Variablen zu
        {
            $this->Modules[] = new $classname($id);
        }
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone()
    {
    }

    /**
     * Singletons should not be restorable from strings.
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * This is the static method that controls the access to the singleton
     * instance. On the first run, it creates a singleton object and places it
     * into the static field. On subsequent runs, it returns the client existing
     * object stored in the static field.
     *
     * This implementation lets you subclass the Singleton class while keeping
     * just one instance of each subclass around.
     * 
     * @return static
     */
    public static function getInstance(): ControllerBase
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls]))
        {
            // Note that here we use the "static" keyword instead of the actual
            // class name. In this context, the "static" keyword means "the name
            // of the current class". That detail is important because when the
            // method is called on the subclass, we want an instance of that
            // subclass to be created here.
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * Ermittelt zur Laufzeit die vorhandenen Modultypen
     * @return array mit Infos zum Modultyp
     */
    public function getModulesAvailable(): array
    {
        $scope = static::getScope();
        $files = glob("{$scope}" . DIRECTORY_SEPARATOR . "*{$scope}.php");

        $result = [];
        foreach ($files as $file)
        {
            $filename = basename($file, ".php");

            $class = $this->getNamespace() . $filename;
            // Schneide das Suffix ...Filter ...Source etc vom ClassName weg
            // $classname = str_replace($class, $ns, "");

            $result[] = [
                'Name' => $file,
                'TypeName' => $class::getTypeName(),
                'ClassName' => $class,
                "Description" => $class::getDescription()
            ];
        }
        return $result;
    }

    /**
     * Listet alle bekannten (=konfigurierten) Module auf
     * @return array Modulinfos
     */
    public function getModulesRegistered(): array
    {
        $result = [];

        foreach ($this->Modules as $module)
        {
            $result[] = [
                'Id' => $module->getId(),
                'Name' => $module->getName(),
                'TypeName' => $module::class::getTypeName(),
                'ClassName' => $module::class,
                'Description' => $module::class::getDescription()];
        }

        return $result;
    }

    /**
     * Fügt ein neues Modul hinzu
     * @param mixed $Name       Name des Moduls
     * @param mixed $TypeName  Typ des Moduls
     * @return int              ErrorCode
     */
    public final function addModule($Name, $TypeName): int
    {
        if (empty($Name) || empty($TypeName))
            return ErrorCodes::NotEnoughArguments;

        if ($this->findModuleBy($Name) != null)
            return ErrorCodes::AlreadyExists;

        $availableModules = $this->getModulesAvailable();
        $availableModulesKeyValue = array_column($availableModules, "ClassName", "TypeName");
        if (!key_exists($TypeName, $availableModulesKeyValue))
        {
            return ErrorCodes::ModulNotFound;
        }
        $classname = $availableModulesKeyValue[$TypeName];
        $this->Modules[] = $classname::register($Name);

        return ErrorCodes::OK;
    }

    /**
     * Findet ein registriertes Modul über den angegebenen Namen
     * @param string $ModuleName    Name des Moduls
     * @return ModuleBase|null      Modulinstanz oder Null wenn nicht gefunden
     */
    private function findModuleBy(string $ModuleName): ModuleBase|null
    {
        foreach ($this->Modules as $module)
        {
            if ($module->getName() == $ModuleName)
                return $module;
        }
        return null;
    }
    /**
     * Gibt den Hilfe-Text des Moduls zurück
     * @param string $ModuleName    Name des Moduls
     * @return string               Hilfetext
     */
    public function getHelp(string $ModuleName): string
    {
        return $this->findModuleBy($ModuleName)?->getDescription();
    }

    /**
     * Gibt die erweiterete Info zu den Parametern zurück
     * @param string $ModuleName    Name des Moduls
     * @return array|null           Parameter-Info-Struktur oder Null wenn nicht gefunden
     */
    public function getParameterInfo(string $ModuleName): ?array
    {
        return $this->findModuleBy($ModuleName)?->getParameterInfo();
    }


    /**
     * Gibt den Wert eines Parameters zurück
     * @param string $ModuleName    Name des Moduls
     * @param string $Parameter     Name des Parameters
     * @return mixed                Inhalt des Parameters
     */
    public function getParameter(string $ModuleName, string $Parameter): mixed
    {
        return $this->findModuleBy($ModuleName)?->getParameter($Parameter);
    }

    /**
     * Setzt einen Parameter auf den angegebenen Wert.
     * @param string $ModuleName    Name des Moduls
     * @param string $Parameter     Name des Parameters
     * @param mixed $Value          Zu setzender Wert
     * @return int|null             ErrorCode oder NULL wenn nicht gefunden
     */
    public function setParameter(string $ModuleName, string $Parameter, mixed $Value): ?int
    {
        return $this->findModuleBy($ModuleName)?->setParameter($Parameter, $Value);
    }

    /**
     * Aktiviert oder deaktiviert ein Modul
     * @param string $ModuleName    Name des Moduls
     * @param bool $Enabled         Flag ob das Modul enabled werden soll oder nicht
     * @return int|null             ErrorCode oder NULL wenn nicht gefunden
     */
    public function setEnabled(string $ModuleName, bool $Enabled): ?int
    {
        return $this->findModuleBy($ModuleName)?->setEnabled($Enabled);
    }

    /**
     * Setzt den Namen eines Moduls
     * @param string $ModuleName    Name des Moduls
     * @param string $Name          Der neue Name des Moduls
     * @return int|null             ErrorCode oder NULL wenn nicht gefunden
     */
    public function setName(string $ModuleName, string $Name): ?int
    {
        return $this->findModuleBy($ModuleName)?->setName($Name);
    }

    /**
     * Setzt alle alten, unbearbeiteten Nachrichten für dieses Modul auf bearbeitetet
     * @param string $ModuleName    Name des Moduls
     * @return int|null             ErrorCode oder NULL wenn nicht gefunden
     */
    public function catchUp(string $ModuleName): ?int
    {
        return $this->findModuleBy($ModuleName)?->catchUp();
    }
    
    /**
     * Startet die Verarbeitung aller Module
     * @param bool $SingleRun    Flag ob alle oder nur eine Nachricht verarbeitet werden soll
     * @return void
     */
    public function run(bool $SingleRun) :void
    {
        foreach ($this->Modules as $modul)
        {
            $modul->run($SingleRun);
        }
    }
}