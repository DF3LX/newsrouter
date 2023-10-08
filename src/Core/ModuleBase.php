<?php

namespace DARCNews\Core;

/**
 * Modulbae ist die Basisklasse für alle Modulimplementierungen
 * Üblicherweise gibt es noch eine Modultyp-spezifische Basisklasse
 * @see SourceBase
 * @see FilterBase
 * @see FormatterBase
 * @see ChannelBase
 * 
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @author Gerrit, DH8GHH <dh8ghh@darc.de>
 * @copyright 2023 Gerrit Herzig, DH8GHH für den Deutschen Amateur-Radio-Club e.V.
 */
abstract class ModuleBase
{
    use ScopeTrait;


   /** @var int $_id Interne, primäre ID des Moduls */
    private int $_id;

    /** @var string $_name Der konfigurierte name des Moduls */
    private string $_name;

    /** @var bool $_enabled Flag, ob das Modul aktiviert ist  */
    private bool $_enabled = false;

    /** @var bool $_catchUpMode Flag, ob das Modul im CatchUp Modus (Dummy-Verarbeitung) ist  */
    private bool $_catchUpMode = false;

    /** @var array $parameters Array mit den Modulspezifischen Parametern */
    protected array $parameters = [];


    /**
     * Statische Beschreibung des Modultyps
     * @return string Beschreibung
     */
    public abstract static function getDescription(): string;

    /**
     * Prüft, ob alle Vorbedingungen (Konfiguration) erfüllt sind und das Modul aktiviert werden kann
     * @return bool     TRUE wenn alle Vorbedingungen erfüllt sein, sonst FALSE
     */
    protected abstract function canEnable(): bool;


    /**
     * Instanziiert das Modul und Lädt die Konfiguration
     * @param int $id   ID des Moduls
     */
    function __construct(int $id)
    {
        $this->_id = $id;

        $this->load();
    }

    /**
     * Gibt den Namen des Moduls zurück
     * @return string Name des Moduls
     */
    public final function getName(): string
    {
        return $this->_name;
    }

    /**
     * Setzt den Namen des Moduls
     * @param string $Name  Der neue Name des Moduls
     * @return string       ErrorCode
     */
    public final function setName(string $Name): int
    {
        $this->_name = $Name;
        $this->save();

        return ErrorCodes::OK;
    }

    /**
     * Gibt die ID des Moduls zurück
     * @return int  Die ID des Moduls
     */
    public final function getId(): int
    {
        return $this->_id;
    }

    /**
     * Gibt zurück, ob das Modul aktiviert ist oder nicht
     * @return bool TRUE wenn aktiviert, FALSE wenn nicht aktiviert
     */
    public function getEnabled(): bool
    {
        return $this->_enabled;
    }

    /**
     *Aktiviert oder deaktivert das Modul und prüft, ob die Vorbedingungen erfpüllt sind
     * @param bool $Enabled Flag, ob das Modul aktiviert (TRUE) oder deaktiviert (False) werden soll
     * @return int          ErrorCode ob die Aktivierung erfolgreich war
     */
    public function setEnabled(bool $Enabled): int
    {
        if ($Enabled && $this->canEnable() === false)
        {
            return ErrorCodes::Operation_Failed;
        }

        $this->_enabled = $Enabled;
        $this->save();

        return ErrorCodes::OK;
    }
    /**
     * Registriert das Modul im Controller und gibt es zurück
     * @param string $Name  Der Name des Moduls
     * @return static       Instanz des Moduls
     */
    public static final function register(string $Name): static
    {
        global $pdo;
        $statement = $pdo->prepare("INSERT INTO " . static::getScope() . "(Name, ClassName) VALUES(:name, :classname) RETURNING id");
        $statement->bindValue(":name", $Name);
        $statement->bindValue(":classname", static::class);
        $statement->execute();
        $id = $statement->fetchColumn();
        $statement->closeCursor();
        $statement = null;

        // gleich instanziieren
        return new static($id);
    }


    /**
     * Lädt die gespeicherte Konfiguration des Moduls
     * @throws \DomainException Falls die Klasse des Moduls nicht passt
     * @return array|null       Array mit den Settings oder NULL wenn keine da
     */
    protected function load(): ?array
    {
        global $pdo;
        // Konfiguration für das Modul laden und den Namespace berücksichten
        $statement = $pdo->prepare("SELECT * FROM " . static::getScope() . " where Id = :id");
        $statement->bindValue(":id", $this->_id);
        $statement->execute();
        $settings = $statement->fetch();
        $statement->closeCursor();
        $statement = null;

        if ($settings)
        {
            if (static::class !== $settings['classname'])
                throw new \DomainException("Instance of " . static::class . " doesn't match {$settings['classname']} for {$settings['name']}");

            $this->_name = $settings['name'];
            $this->_enabled = $settings['enabled'];

            if ($settings['parameters'] != null)
            {
                // in den Parameters werden die Settings nur als Key => Value array abgespeichert.
                // Deswegen wird das hier wieder zurück gemappt
                foreach (json_decode($settings['parameters'], true) as $key => $value)
                {
                    // Nur das mappen, was auch existiert, sonst bumm
                    if (array_key_exists($key, $this->parameters))
                        $this->parameters[$key]['Value'] = $value;
                }
            }
            return $settings; // zur weiteren Verarbeitung durch Kindklassen
        }

        return null;    // keine gespeicherten Parameter
    }

    /**
     * Speichert die Konfiguration des Moduls in die Datenbank
     * @return void Nix, gar nix
     */
    private function save(): void
    {
        // Nimmt nur Spalte "Value" aus dem mehrdimensionalen parameters-Array und setze die Keys davor
        $settings = array_combine(array_keys($this->parameters), array_column($this->parameters, "Value"));

        global $pdo;
        // Konfiguration in die für diesen Namespace zuständige Tabelle speichern
        $statement = $pdo->prepare("UPDATE " . static::getScope() . " SET enabled = :enabled, name = :name, parameters = :parameters WHERE id = :id");
        $statement->bindValue(":enabled", $this->_enabled ? 1 : 0);
        $statement->bindValue(":name", $this->_name);
        $statement->bindValue(":parameters", json_encode($settings));
        $statement->bindValue(":id", $this->getId());
        $statement->execute();
        $statement->closeCursor();
        $statement = null;
    }


    /**
     * Gibt zurück, ob das Modul im CatchUp-Modus ist
     * @return bool TRUE wenn CatchUp ansonsten FALSE
     */
    protected final function isCatchUp(): bool
    {
        return $this->_catchUpMode;
    }

    /**
     * Gibt die Parameters-Struktur zurück für eine vollständige Hilfe-Anzeige
     * @return array    Parameters-Struktur
     */
    public function getParameterInfo(): array
    {
        return $this->parameters;
    }

    /**
     * Gibt den Wert eines Parameters aus
     * @param string $Parameter Name des Parameters
     * @return mixed            Wert des Parameters
     */
    public function getParameter(string $Parameter): mixed
    {
        // finde einenparameter, der so anfängt wie die Nutzereingabe
        foreach (array_keys($this->parameters) as $key)
        {
            if (strncasecmp($key, $Parameter, strlen($Parameter)) == 0)
            {
                if ($this->parameters[$key]['Encrypt'] ?? false)
                {
                    require "config.php";
                    $c = base64_decode($this->parameters[$key]['Value']);
                    $ivlen = openssl_cipher_iv_length($cipher = "aes-128-cbc");
                    $iv = substr($c, 0, $ivlen);
                    $hmac = substr($c, $ivlen, $sha2len = 32);
                    $ciphertext_raw = substr($c, $ivlen + $sha2len);
                    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, base64_decode($CryptKey), $options = OPENSSL_RAW_DATA, $iv);
                    $calcmac = hash_hmac('sha256', $ciphertext_raw, base64_decode($HashKey),  true);

                    unset($CryptKey);
                    unset($HashKey);

                    if (hash_equals($hmac, $calcmac)) // Rechenzeitangriff-sicherer Vergleich
                    {
                        return unserialize($original_plaintext);
                    }
                    return ""; // Crypto Error
                }
                else
                    return $this->parameters[$key]['Value'];
            }
        }

        // Wenns den Parameter nicht gibt, ausnahmsweise keinen ErrorCode, sondern leer. 
        // In dem Parameter könnte ja alles drin sein.
        return "";
    }



    /**
     * Setzt einen parameter auf den angegebenen Wert
     * Wenn der Parameter ein Multivalue-Wert ist, wird das erste Zeichen des Werts ausgewertet.
     * mit + werden Einträge hinzugefügt
     * mit - wird der Eintrag entfernt.
     * mit ! wird die Liste gelöscht
     * @param string $Parameter Name des Parameters
     * @param mixed $Value      Wert der gesetzt werden soll
     * @return string           ErrorCode der Aktion
     */
    public function setParameter(string $Parameter, mixed $Value): int
    {
        // finde einen Parameter, der so anfängt wie die Nutzereingabe
        $treffer = 0;
        foreach (array_keys($this->parameters) as $key)
        {
            if (strncasecmp($key, $Parameter, strlen($Parameter)) == 0)
            {
                $Parameter = $key; // Setze Parameter auf den "richtigen" Namen
                $treffer++;
            }
        }
        // Keinen eindeutigen Treffer? tja, sorry
        if ($treffer != 1)
            return ErrorCodes::ParameterNotFound;

        // Multivalue parameter verarbeiten
        if ($this->parameters[$Parameter]['MultiValue'] ?? false)
        {
            $parameterValue = $this->getParameter($Parameter) ?? array();

            switch ($Value[0])
            {
                case '-': // entfernen
                    if (($key = array_search(trim(substr($Value, 1)), $parameterValue)) !== false)
                        unset($parameterValue[$key]);
                    break;

                case '+': // hinzufügen
                    $Value = trim(substr($Value, 1)); // entferne das +

                    $parameterValue[] = $Value;
                    break;

                case '!': // leeren
                    $parameterValue = [];
                    break;

                default:
                    return ErrorCodes::MultiValueNoOperator;
            }

            $Value = $parameterValue; // Das wollen wir ja eigentlich schreiben
        }

        if ($this->parameters[$Parameter]['Encrypt'] ?? false)
        {
            require "config.php";
            $ivlen = openssl_cipher_iv_length($cipher = "aes-128-cbc");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt(serialize($Value), $cipher, base64_decode($CryptKey), $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, base64_decode($HashKey), true);
            $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
            $this->parameters[$Parameter]['Value'] = $ciphertext;
            unset($CryptKey);
            unset($HashKey);
        }
        else
            $this->parameters[$Parameter]['Value'] = $Value;

        $this->save();

        return ErrorCodes::OK;
    }



    /**
     * Mit CatchUp werden Nachrichten dummy-verarbeitet, um ein nachträglich hinzugefügtes
     * Modul auf den aktuellen Stand zu bringen, ohne tonnenweise Nachrichten zu erzeugen.
     * In der Regel läuft doStuff normal durch, die Nachrichten werden aber in einem anderen Status gespeichert.
     * 
     * @return int  ErrorCode, ob CatchUp erfolgreich war
     */
    public function catchUp(): int
    {
        // CatchUp nur möglich wenn nicht enabled
        if ($this->_enabled)
            return ErrorCodes::Operation_Failed;

        if (!$this->canEnable())
            return ErrorCodes::NotReady;

        $this->_catchUpMode = true;
        // doStuff ausführen, bis alles aufgeholt ist
        while ($this->doStuff() > 0)
            ;   // ja, das ist ein ; und das muss da hin: While... do nothing
        $this->_catchUpMode = false;

        return ErrorCodes::OK;
    }


    /**
     * Modulspezifische Implementierung der Nachrichtenverarbeitung
     * @return int Anzahl der verarbeiteten Nachrichten für "run()"
     * @see ModuleBase::run
     */
    protected abstract function doStuff(): int;

    /**
     * Startet die Verarbeitung in einem Modul
     * @param bool $SingleRun   Flag, ob nach der erfolgreichen Verarbeitung der Lauf noch einmal gestartet werden soll
     * @return void             nix, gar nix
     */
    public final function run(bool $SingleRun): void
    {
        Logger::Info("Run in {$this->getName()} aufgerufen\n");

        while (true)
        {
            if (!$this->getEnabled())   
            {
                Logger::Warn("Modul {$this->getName()} ist nicht enabled");
                break; // run nicht möglich wenn nicht enabled
            }
            if ($this->doStuff() < 1)
            {
                Logger::Info("Modul {$this->getName()} hat nix zu tun");
                break; // run lohnt nicht wenn doStuff keinen Erfolg meldet
            }

            if ($SingleRun)
            {
                Logger::Info("Run wurde mit SingleRun gestartet. Keine Wiederholung des Moduls {$this->getName()}.");
                break; // ende wenn SingleRun
            }
        }
    }
}