<?php

/**
 *
 * 2007-03-13 Developement begin
 *
 * @version 0.1
 * @author Michal Ivanic
 */
class dbException extends Exception {
    public $backtrace;

    public function __construct($message=false, $code=false) {
        if (!$message) {
            $this->message = pg_last_error();
        }

        if (!$code) {
            $this->code = pg_last_error();
        }

        $this->backtrace = debug_backtrace();
    }
}


/**
 * PostgreSQL Class by Michal Ivanic, edited by Matej Svetlik (tim Fenix)
 * Nasledne upravovali timy: Backspace, Bughunters
 *
 * 2007-12	Rewriten to be used with Postgresql
 * 2007-03-07 MySQL class
 *
 * @version 2
 * @author Michal Ivanic, Matej Svetlik
 */
class db
{
    private $db_host    = "";  			// server name
    private $db_user    = "";       	// user name
    private $db_pass    = "";           // password
    private $db_dbname  = "";           // database name
    private $db_charset = "utf8";       // character set (optional)
    private $db_pcon    = false;        // use persistent connection?

    // class-internal variables - do not change
    private $active_row     = -1;       // current row
    private $error_desc     = "";       // last mysql error string
    private $in_transaction = false;    // used for transactions
    private $last_insert_id;            // last id of record inserted
    private $last_result;               // last mysql query result
    private $last_sql       = "";       // last mysql query
    private $link_id        = 0;        // mysql link id
    private $time_diff      = 0;        // holds the difference in time
    private $time_start     = 0;        // start time for the timer
    private $table_name     = '';

    public $sql_history = array();

    /**
     * Constructor: Opens the connection to the database
     *
     * @param String 	$host	Host address
     * @param String 	$user	User name
     * @param String	$pass	Password
     * @param String	$dbname	Database name
     * @param String 	$charset (Optional) Character set
     * @param Boolean	$pcon	(Optional) Persistant connection
     */
    public function __construct($host, $user, $pass, $dbname, $charset="", $pcon=false)
    {
        $this->db_host = $host;
        $this->db_user = $user;
        $this->db_pass = $pass;
        $this->db_dbname = $dbname;

        if (strlen($charset) > 0) {
            //$this->db_charset = $charset;
        }
        else {
            //$this->db_charset = "utf8";
        }

        $this->db_pcon = $pcon;

        $result = $this->connect();

        // nie je potrebne nadalej uchovat
        // zbytocne nezverejnovat (vo var_dump objektu by bolo vidno) ak netreba
        $this->db_pass = "";
    }

    /**
     * Destructor: Closes the connection to the database
     */
    public function __destruct()
    {
        try
        {
            $result = $this->close();
        }
        catch (dbException $e) {

        }
    }

    /**
     * Connect to specified MySQL server
     *
     */
    public function connect()
    {
        $this->active_row = -1;

        $conn_string = "host='$this->db_host' port='5432' dbname='$this->db_dbname' user='$this->db_user' password='$this->db_pass'";

        $this->link_id = @pg_connect ($conn_string);

        // connect to mysql server failed?
        if (!is_resource($this->link_id))
        {
            $this->error_desc  = $this->db_pcon ? "Persistent " : "";
            $this->error_desc .= "Connect failed";

            throw new dbException;
        }

        pg_set_client_encoding  ( $this->link_id  , $this->db_charset  );
    }

    /**
     * Closes current connection
     */
    public function close()
    {
        $this->active_row = -1;
        if (!@pg_close($this->link_id)) {
            throw new dbException;
        }
    }


    /**
     * Returns the last error as text
     *
     * @return String Error text from last known error
     */
    public function error()
    {
        if (!empty($this->error_desc)) return $this->error_desc;

        if (empty($this->link_id))
        {
            $this->error_desc = "No connection";
            $em = $this->error_desc;
        }
        else
        {
            $en = @pg_last_error($this->link_id);
            $em = @pg_last_error($this->link_id);
            if (strlen($en) > 0)
            {
                $em = $this->error_desc . " (#" . $en . ")";
            } else {
                $this->error_desc = "";
                $em = $this->error_desc;
            }
        }
        return $em;
    }


    /**
     * Returns the last error as a number
     *
     * @return Integer Error number from last known error
     */
    public function errorNumber()
    {
        $error_number = @pg_last_error($this->link_id);
        if (strlen($this->error_desc) > 0)
        {
            if ($error_number <> 0)
            {
                return $error_number;
            } else {
                return -1;
            }
        } else {
            return $error_number;
        }
    }

    /**
     * Returns true if the internal pointer is at the beginning of the records
     *
     * @return Boolean TRUE if at the first row or FALSE if not
     */
    public function BeginningOfSeek()
    {
        if ($this->active_row < 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns true if the internal pointer is at the end of the records
     *
     * @return Boolean TRUE if at the last row or FALSE if not
     */
    public function EndOfSeek()
    {
        if ($this->active_row >= ($this->RowCount()))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns the last autonumber ID field from a previous INSERT query
     *
     * @return  Integer ID number from previous INSERT query
     */
    public function GetLastInsertID()
    {
        $sequence = $this->table_name . '_id_seq';
        $sql = "SELECT currval('$sequence') as last_id";

        $this->query($sql);

        $row = $this->fetch_assoc();
        return $row['last_id'];
    }

    /**
     * Returns the last SQL statement executed
     *
     * @return String Current SQL query string
     */
    public function GetLastSQL()
    {
        return $this->last_sql;
    }

    /**
     * Determines if a value of any data type is a date PHP can convert
     *
     * @param Date/String $value
     * @return Boolean Returns TRUE if value is date or FALSE if not date
     */
    public function IsDate($value)
    {
        $date = date('Y', strtotime($value));
        if ($date == "1969" || $date == '')
        {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Seeks to the beginning of the records
     *
     */
    public function MoveFirst()
    {
        $this->Seek(0);
        $this->active_row = 0;
    }

    /**
     * Seeks to the end of the records
     *
     */
    public function MoveLast()
    {
        $this->active_row = $this->RowCount() - 1;
        $this->Seek($this->active_row);
    }

    /**
     * Dynamicky vykona _query ale bezpecnejsie _queryParams podla vstupnych argumentov.
     * Ak boli zadane vstupne parametre, vykona sa _queryParams, inac _query.
     * 
     * @param String $sql - dopyt ktory sa ma vykonat
     * @param array $params - vstupne parametre, moze byt jeden prvok alebo pole
     * @param array $conv - zoznam stlpcov pre ktore sa vykona automaticka kovnerzia dat. typov
     * - ak je pole prazdne vykona sa konverzia pre VSETKY stlpce
     * - ak nie je pole (napr. null), kontrola sa preskoci
     * - POZOR na indexaciu v poli su indexovane od 0 a v SQL od 1
     * @see db#inputConversion()
     */
    public function query($sql, $params = array(), $conv=array())
    {       
        // podme vykonat kontrolu parametrov alebo taky maly hack ...
        // ak zada jeden parameter tak ho zabali do pola
        // Pozn. ak nezadal bude empty pole takze dole sa vykona _query
        if (!is_array($params)) $params = array($params);
        
        if (empty($params))
        {
            $this->_query($sql);
        }else{
            $this->_queryParams($sql, $params, $conv);
        }
    }
    
    /**
     * Executes the given SQL query
     *
     * @param String  $sql The query string should not end with a semicolon
     */
    private function _query(&$sql)
    {
        $this->last_sql = $sql;
        $this->TimerStart();
        $this->last_result = @pg_query($this->link_id,$sql);
        $this->TimerStop();
        $this->afterQuery();
    }

    /**
     * Metoda vykona konverziu vstupnych parametrov.
     * Nahradi boolean ekvivaletnym SQL vyrazom
     * 
     * @param array $params - vstupne parametre
     * - ak je pole prazdne vykona sa konverzia pre VSETKY stlpce
     * - ak nie je pole (napr. null), kontrola sa preskoci
     * @param array $conv - zoznam stlpcov pre ktore sa vykona konverzia
     */
    private function inputConversion(&$params, $conv)
    {
        // skip check ak nie je pole
        if (!is_array($conv)) return;
        // ak nespecifikoval ake tak prebehne vsetky
        // POZOR: moze byt casovo narocne
        if (empty($conv)) $conv = array_keys($params);

        foreach ($conv as $key)
        {
            // ak je nezadane tak robit konverziu je blbost
            // isset nie je vhodne kvoli potencialnejnej hodnote null
            if (!array_key_exists($key, $params))
            { 
                trigger_error("Pokus o konverziu nad parametrom `$key`, ktory nevystupuje vo vstupnych parametroch");
                continue;
            }
            $value = &$params[$key];
            // konverzia BOOL->String
            if (is_bool($value))
            {
                $value = $value ? "true" : "false";
            }
        }
    }

    /**
     * Metoda vykona output konverziu datovych typov daneho riadku.
     * Prekonvertuje true a false vyrazy na boolean.
     * 
     * @param array $row - riadok so datami
     * @param array $conv - stlpce pre ktore sa vykona konverzia, 
     * - ak je pole prazdne vykona sa konverzia pre VSETKY stlpce
     * - ak nie je pole (napr. null), kontrola sa preskoci
     * @return unknown_type
     */
    private function outputConversion(&$row, $conv)
    {
        // ak sa nenacital ziaden riadok tak nema co konvertovat
        if ($row === false) return;
        // skip check ak nie je pole
        if (!is_array($conv)) return;
        // ak nespecifikoval ake tak prebehne vsetky
        // POZOR: moze byt casovo narocne
        if (empty($conv)) $conv = array_keys($row);
        
        foreach ($conv as $key)
        {
            // ak je nezadane tak robit konverziu je blbost
            // upravena konverzia tak aby nacitanie stlpca s NULL nerobilo problem
            if (!array_key_exists($key, $row))
            {
                trigger_error("Pokus o konverziu nad stlpcom `$key`, ktory nevystupuje v ziskanom riadku tabulky.");
                continue;
            }
            $value = &$row[$key];
            // konverzia String-> BOOL
            //TODO: otestovat ako to vracia ...
            switch ($value)
            {
                case "t": $value = true; break;
                case "f": $value = false; break;
            }
        }
    }

    /**
     * Vysklada vysledne SQL z dopytu a jednotlivych parametrov.
     * Len na interne ucely pre query()
     * @param String $sql - SQL query
     * @param array $params - parametre
     * @return String
     */
    private function logQuery($sql, $params)
    {
        $params = implode(", ", $params);
        return "{$sql};\n BIND=[{$params}]";
    }

    /**
     * Vykona postprocessing po vykonani query, t.j.
     * - ziskanie nazvu tabulky pre getLastInsertID ak sa vykonal INSERT
     * - ziskanie poctu riadkov ak sa vykonal SELECT (+ak je aktivny DEBUG tak zaznamena vysledok query)
     * - ak query zlyhala vyvola vynimku
     * @return unknown_type
     */
    private function afterQuery()
    {
        if($this->last_result)
        {
            if(preg_match("/^INSERT/i", $this->last_sql))
            {
                $numrows = 0;
                $this->active_row = -1;
                preg_match('/INSERT INTO ([a-zA-Z0-9_]+)/i', $this->last_sql, $matches);
                $this->table_name = $matches[1];

                if(empty($this->table_name)) {
                    die('Aaaaaa som chybny regex');
                }
                	
                return $this->last_result;
            }
            // drobny fix vyrazu aby zaznamenal aj selecty spojene s union apod.
            // Pr. (SELECT ...) UNION (SELECT ...) ORDER BY ...
            else if(preg_match("/^[(]*SELECT/i", $this->last_sql))
            {
                if(DEBUG) {
                    // nacita vsetky riadky a spravi reqind aby boli data k dispozicii
                    $rows = $this->fetchall_assoc();
                    $this->MoveFirst();
                    $this->sql_history[] = array($this->last_sql, $this->TimerDuration(), $rows);
                }
                $numrows = pg_num_rows($this->last_result);
                if ($numrows > 0)
                {
                    $this->active_row = 0;
                } else {
                    $this->active_row = -1;
                }
                $this->last_insert_id = 0;
                return $this->last_result;
            }
        }
        else
        {
            $this->active_row = -1;
            $this->error_desc = "Query failed: ({$this->last_sql})";
            throw new dbException();
        }
    }

    /**
     * Bezpecnejsie varianta vykonanie Query.
     * Zadava sa len query kde parametre su oznacovane $1, $2, $3 ,...
     * a separatne sa odovzdaju parametre. Parametre budu automaticky espacovane podla typu.
     * @param String $query - dopyt
     * @param array $params - parametre, ak sa zadava jediny parameter je mozne ho zadat priamo (nemusi byt sam v poli)
     * @param array $conv - pole so stlpcami pre ktore bude vykonana automaticka konverzia dat. typov
     * @see db#inputConversion
     */
    private function _queryParams(&$query, &$params, &$conv)
    {
        // konverzia
        $this->inputConversion($params, $conv);
        // samotne vykonanie
        $this->last_sql = $this->logQuery($query, $params);
        $this->TimerStart();
        $this->last_result = @pg_query_params($this->link_id, $query, $params);
        $this->TimerStop();
        $this->afterQuery();
    }

    /**
     * Returns the records from the last query
     *
     * @return Object PHP 'result' resource object containing the records
     *                for the last query executed
     */
    public function Records()
    {
        return $this->last_result;
    }

    /**
     * Frees memory used by the query results and returns the function result
     *
     * @return Boolean Returns TRUE on success or FALSE on failure
     */
    public function Release()
    {
        return @pg_free_result($this->last_result);
    }

    //TODO: deprecated funkcie, ponechane len na inspiraciu pre upravu fetch*
    /**
     * Reads the current row and returns contents as a
     * PHP object or returns false on error
     *
     * @param Integer $optional_row_number (Optional) Use to specify a row
     * @return Object PHP object or returns false on error
     */
   /* public function Row($optional_row_number=-1)
    {
        if ($optional_row_number > -1)
        {
            if ($optional_row_number >= $this->RowCount())
            {
                return false;
            }
            $this->active_row = $optional_row_number;
            $this->Seek($optional_row_number);
        }
        else
        {
            $this->active_row++;
            if ($this->active_row > ($this->RowCount()))
            {
                return false;
            }
        }

        if ($this->last_result)
        {
            $row = pg_fetch_object($this->last_result);
        } else {
            $row = false;
        }
        return $row;
    }

    /**
     * Reads the current row and returns contents as an
     * array or returns false on error
     *
     * @param Integer $optional_row_number (Optional) Use to specify a row
     * @return Array Array that corresponds to fetched row or FALSE if no rows
     */
   /* public function RowArray($optional_row_number=-1)
    {
        if ($optional_row_number > -1)
        {
            if ($optional_row_number >= $this->RowCount())
            {
                return false;
            }
            $this->active_row = $optional_row_number;
            $this->Seek($optional_row_number);
        }
        else
        {
            $this->active_row++;
            if ($this->active_row == ($this->RowCount()))
            {
                return false;
            }
        }

        if ($this->last_result)
        {
            $row = pg_fetch_array($this->last_result);
        } else {
            $row = false;
        }
        return $row;
    }

    /**
     * Returns the last query's number of rows
     *
     * @return Integer Row count
     */
    public function RowCount()
    {
        if (!$this->last_result)
        {
            return 0;
        }
        else
        {
            return @pg_num_rows($this->last_result);
        }
    }

    /**
     * Sets the internal database pointer to the
     * specified row number and returns the result
     *
     * @param Integer $row_number Row number
     * @return Object Fetched row as PHP object
     */
    public function Seek($row_number)
    {
        if ($row_number >= $this->RowCount())
        {
            return false;
        }
        $this->active_row = $row_number;
        return pg_result_seek($this->last_result, $row_number);
    }

    /**
     * Returns string suitable for SQL
     *
     * @param String  $value
     * @return String SQL formatted value
     */
    public function SQLFix($value)
    {
        return @pg_escape_string($value);
    }

    /**
     * Returns MySQL string as normal string
     *
     * @param String  $value
     * @return String
     */
    public function SQLUnfix($value)
    {
        return $value; // neni treba ?
    }

    /**
     * Returns last measured duration (time between TimerStart and TimerStop)
     *
     * @param Integer $decimals (Optional) The number of decimal places to show
     * @return Float Microseconds elapsed
     */
    public function TimerDuration($decimals=4)
    {
        return number_format($this->time_diff,$decimals);
    }

    /**
     * Starts time measurement (in microseconds)
     *
     */
    public function TimerStart()
    {
        $parts = explode(" ",microtime());
        $this->time_diff  = 0;
        $this->time_start = $parts[1].substr($parts[0],1);
    }

    /**
     * Stops time measurement (in microseconds)
     *
     */
    public function TimerStop()
    {
        $parts  = explode(" ",microtime());
        $time_stop = $parts[1].substr($parts[0],1);
        $this->time_diff  = ($time_stop - $this->time_start);
        $this->time_start = 0;
    }

    /**
     * Starts a transaction
     * @return dbException ak je v traksakcii alebo sa transakciu nepodarilo zacat
     */
    public function TransactionBegin()
    {
        if(!$this->in_transaction)
        {
            try
            {
                $this->query("BEGIN");
                $this->in_transaction = true;
            }
            catch (dbException $e) {
                $this->error_desc = "Could not begin transaction";
                throw new dbException;
            }
        }
        else
        {
            $this->error_desc = "Already in transaction";
            throw new dbException;
        }
    }

    /**
     * Ends a transaction and commits the queries
     *
     * @return dbException id COMMIT failed (+automatic ROLLBACK)
     */
    public function TransactionEnd()
    {
        if($this->in_transaction)
        {
            try
            {
                $this->query("COMMIT");
                $this->in_transaction = false;
            }
            catch (dbException $e) {
                $this->TransactionRollback();
                $this->error_desc = "Could not end transaction";
                throw new dbException;
            }
        }
        else
        {
            $this->error_desc = "Not in a transaction";
            throw new dbException;
        }
    }

    /**
     * Rolls the transaction back
     */
    public function TransactionRollback()
    {
        try
        {
            $this->query("ROLLBACK");
            // transakcia skoncila
            // inac ak by sme robili dalsiu tak TransactionBegin hodi exception
            $this->in_transaction = false;
        }
        catch (dbException $e) {
            $this->error_desc = "Could not rollback transaction";
            throw new dbException;
        }
    }

    /**
     * Returns result as enumerated array.
     *
     * @param array $conv - zoznam stlpcov pre ktore bude vykonana automaticka konverzia
     * (v poli musia byt pouzite indexy nie nazvy stlpcov)
     * - ak je pole prazdne vykona sa konverzia pre VSETKY stlpce
     * - ak nie je pole (napr. null), kontrola sa preskoci
     * @return array
     * @see db#outputConversion
     */
    public function fetch_row($conv = array()) {
        if (!$this->last_result)
        {
            throw new dbException("No result to fetch");
        }
        $row = pg_fetch_row($this->last_result);
        $this->active_row++;
        
        $this->outputConversion($row, $conv);

        return $row;
    }

    /**
     * Returns result as an associative array
     *
     * @param array $conv - zoznam stlpcov pre ktore bude vykonana automaticka konverzia
     * (musi obsahovat zoznam nazvov stlpcov pre ktore sa vykona konverzia)
     * - ak je pole prazdne vykona sa konverzia pre VSETKY stlpce
     * - ak nie je pole (napr. null), kontrola sa preskoci
     * @return array
     * @see db#outputConversion
     */
    public function fetch_assoc($conv = array())
    {
        if (!$this->last_result)
        {
            throw new dbException("No result to fetch");
        }
        $row = pg_fetch_assoc($this->last_result);
        $this->active_row++;
        
        $this->outputConversion($row, $conv);

        return $row;
    }

    /**
     * Returns all results as an associative array
     *
     * @param array $conv - zoznam stlpcov pre ktore bude vykonana automaticka konverzia
     * (musi obsahovat zoznam nazvov stlpcov pre ktore sa vykona konverzia)
     * - ak je pole prazdne vykona sa konverzia pre VSETKY stlpce
     * - ak nie je pole (napr. null), kontrola sa preskoci
     * @see db#outputConversion
     * @return unknown
     */
    public function fetchall_assoc($conv = array())
    {
        $this->MoveFirst();

        $retval = array();
        // tu sa konverzia nemusi vykonavat lebo to robi fetch_assoc
        while ($row = $this->fetch_assoc($conv)) $retval[] = $row;

        return $retval;
    }
    
    /*
     * Convenience wrapper ak by dakto pouzil povodne nech ho upozorni na chybu ale vykona 
     */
    public function queryParams($query, $params)
    {
        // kontrolu treba spravit tu lebo normalne ju robi uz query
        if (!is_array($params)) $params = array($params);
        $this->_queryParams($query, $params);
        trigger_error("Method queryParams is deprecated. Use query instead.", E_USER_WARNING);
    }
}
?>
