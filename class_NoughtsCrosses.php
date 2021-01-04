<?php
class NoughtsCrosses
{
    // Database Connection
    private $hostname = "******"; // remote DB IP Address / localhost
    private $database = "******"; // database name
    private $username = "******"; // DB user name
    private $password = "******"; // DB password

    // Players
    public $xWins = 0;
    public $oWins = 0;
    public $draws = 0;

    public function create_table()
    {
        //Database Connection
        $db = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
        if(mysqli_connect_errno()){
            echo 'Connection Failed '.mysqli_connect_errno();
        }

        $sql = "CREATE TABLE IF NOT EXISTS games(
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                cellA1 char(1) DEFAULT NULL,
                cellA2 char(1) DEFAULT NULL,
                cellA3 char(1) DEFAULT NULL,
                cellB1 char(1) DEFAULT NULL,
                cellB2 char(1) DEFAULT NULL,
                cellB3 char(1) DEFAULT NULL,
                cellC1 char(1) DEFAULT NULL,
                cellC2 char(1) DEFAULT NULL,
                cellC3 char(1) DEFAULT NULL,
                winner char(1) DEFAULT NULL,
                PRIMARY KEY (id))
                ";

        if(mysqli_query($db, $sql)){
            echo "Table games created successfully, ready to play? \n";
        } else {
            echo "Could not create table: ". mysqli_connect_errno() . "\n";
        }

    }

    public function calculate_winners()
    {
        // Database Connection
        $db = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
        if(mysqli_connect_errno()){
            echo 'Connection Failed '.mysqli_connect_errno();
        }

        $i = 0;

        $stdin = fgets(STDIN);
        do{
                if($stdin[0] != "\n"){
                    $row[$i] = array($stdin[0],$stdin[1],$stdin[2]);
                    if($i < 2){
                        ++$i;
                    }else{
                        // Create Board
                        $cellA1 = strtoupper((string)$row[0][0]);
                        $cellA2 = strtoupper((string)$row[0][1]);
                        $cellA3 = strtoupper((string)$row[0][2]);
                        $cellB1 = strtoupper((string)$row[1][0]);
                        $cellB2 = strtoupper((string)$row[1][1]);
                        $cellB3 = strtoupper((string)$row[1][2]);
                        $cellC1 = strtoupper((string)$row[2][0]);
                        $cellC2 = strtoupper((string)$row[2][1]);
                        $cellC3 = strtoupper((string)$row[2][2]);
                        //print_r ($row[0]);
                        //echo $cellA1;

                        // Horizontal Wins
                        if($cellA1 == $cellA1 && $cellA2 == $cellA3){
                            $winner = $cellA1;
                        }elseif($cellB1 == $cellB2 && $cellB1 == $cellB3){
                            $winner = $cellB1;
                        }elseif($cellC1 == $cellC2 && $cellC1 == $cellC3){
                            $winner = $cellC1;

                        // Vertical Wins
                        }elseif($cellA1 == $cellB1 && $cellA1 == $cellC1){
                            $winner = $cellA1;
                        }elseif($cellA2 == $cellB2 && $cellA2 == $cellC2){
                            $winner = $cellA2;
                        }elseif($cellA3 == $cellB3 && $cellA3 == $cellC3){
                            $winner = $cellA3;

                        // Diagonal Wins
                        }elseif($cellA1 == $cellB2 && $cellA1 == $cellC3){
                            $winner = $cellA1;
                        }elseif($cellA3 == $cellB2 && $cellA3 == $cellC1){
                            $winner = $cellA3;
                        }else{
                            $winner = "D";
                        }

                        switch($winner) {
                            case "X":
                                ++$this->xWins;
                                break;
                            case "O":
                                ++$this->oWins;
                                break;
                            default:
                                ++$this->draws;
                            }

                        $sql = "INSERT INTO games
                                (cellA1, cellA2, cellA3, cellB1, cellB2, cellB3, cellC1, cellC2, cellC3, winner)" .
                                "VALUES
                                ('$cellA1','$cellA2','$cellA3','$cellB1','$cellB2','$cellB3','$cellC1','$cellC2','$cellC3','$winner'
                                )";

                        $result = mysqli_query($db,$sql);

                        if (!$result) exit($db->error);
                        $i = 0;
                    }
                }
            }
        while (false !== ($stdin = fgets(STDIN)));
    }

    public function get_results()
    {
        echo "X wins: " . $this->xWins . "\n";
        echo "O wins: " . $this->oWins . "\n";
        echo "Draws: " . $this->draws . "\n";
    }

    public function get_aggregate_results()
    {
        // Database Connection
        $db = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
        if(mysqli_connect_errno()){
            echo 'Connection Failed '.mysqli_connect_errno();
        }

        $sql  = "SELECT winner,
                    COUNT(winner) as total 
                    FROM games
                    GROUP BY winner
                    ORDER BY winner DESC
                    ";

        $result = mysqli_query($db,$sql);
        $rows = $result->num_rows;

        for($i=0; $i<$rows; ++$i)
        {
            mysqli_data_seek($result, $i);
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            if($row['winner'] == "D") {
                echo "Draws: " . $row['total'] . "\n";
            } else {
                echo $row['winner'] . " wins: " . $row['total'] . "\n";
            }
        }
    }

}
