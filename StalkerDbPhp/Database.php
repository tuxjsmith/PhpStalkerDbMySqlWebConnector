<?php

/*
 * Copyright (c) 2005, tuxjsmith@gmail.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * TODO: Initialise all variables at the top of functions.
 * TODO: I find things too confusing otherwise.
 */

/*
 * Adds a 'connection' key to the built in associate array $GLOBALS
 */
global $connection;

/*
 * Make a connection to the database.
 */
//<editor-fold defaultstate="collapsed" desc="dbConnect">
function dbConnect() {

    $GLOBALS['connection'] = new mysqli( "localhost", "testUser", "foobar", "followingCars" );

    if ( $GLOBALS[ 'connection' ] -> connect_errno ) {

        echo "[1] Failed to connect to MySQL: (" . $GLOBALS['connection']->connect_errno . ") " . $GLOBALS['connection']->connect_error;

        echo PHP_EOL;

        exit;
    }

    if ( !$GLOBALS['connection'] -> set_charset( 'utf8' ) ) {

        echo "Error loading character set utf8: %s\n", $GLOBALS[ 'connection' ] -> error;

        echo PHP_EOL;

        exit;
    }
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="dbConnect_setDeleted">
function dbConnect_setDeleted () {

    dbConnect ();

    $id = mysqli_real_escape_string ( $GLOBALS[ 'connection' ], filter_input ( INPUT_POST, 'id' ) );

    $GLOBALS[ 'connection' ] -> query (
            "update data set deleted = 1 where id = " . $id );

    deletedFeedback ();

    $GLOBALS [ 'connection' ] -> close ();
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="dbConnect_update">
function dbConnect_update () {

    dbConnect ();

    /*
     * URL received parameters.
     * 
     * Variables: $reg, $make, etc
     * are retrieved from the URL post ($_POST) values.
     */   
    $reg = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'reg' ) );
    $make = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'make' ) );
    $date = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'date' ) );
    $location = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'location' ) );
    $fileName = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'fileName' ) );
    $notes = mysqli_real_escape_string ($GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'notes' ) );
    $id = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'id' ) );
    $crimeStoppers = mysqli_real_escape_string ($GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'crimeStoppers' ) );
    $imageMime = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'imageMime' ) );
    $type = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'vehicleType' ) );

    /*
     * dbConnect_update (edit & save)
     * 
     * CrimeStoppers insert
     * table: crimeStoppers.
     */
    //<editor-fold defaultstate="collapsed" desc="dbConnect_update :: crimeStoppers">
    $cs_result = $GLOBALS [ 'connection' ] -> query (
            "select count(*) from crimeStoppers where reg = '" . filter_input ( INPUT_POST, 'reg' ) . "'" );

    $regCounter = 0;

    if ( $cs_result ) {

        while ( $row = $cs_result -> fetch_assoc () ) {

            $regCounter = $row [ 'count(*)' ];
        }

        $cs_result -> close ();
    }

    /*
     * If no entries match the reg' in crimeStoppers table then insert a new record.
     */
    if ( $regCounter == 0 ) {

        $GLOBALS [ 'connection' ] -> query (
                "insert into crimeStoppers (reg, reported) values ('" . $reg . "', " . $crimeStoppers . ")" );
    }
    /*
     * If count is > 0 then update the record that matches the reg'.
     */ 
    else {

        $GLOBALS [ 'connection' ] -> query (
                "update crimeStoppers set reported = " . $crimeStoppers . " where reg = '" . $reg . "'" );
    }
    //</editor-fold>
    
    /*
     * dbConnect_update (edit & save)
     * 
     * Vehicle make insert
     * table: vehicleMake.
     */
    //<editor-fold defaultstate="collapsed" desc="dbConnect_update :: vehicleMake">
    $vm_result = $GLOBALS [ 'connection' ] -> query (
            "select count(*) from vehicleMake where reg = '" . $reg . "'" );

    $vm_counter = 0;

    if ( $vm_result ) {

        while ( $vm_row = $vm_result -> fetch_assoc () ) {

            $vm_counter = $vm_row [ 'count(*)' ];
        }

        $vm_result -> close ();

        if ( $vm_counter == 0 ) {

            $GLOBALS [ 'connection' ] -> query (
                    "insert into vehicleMake (reg, make) values ('" . $reg . "', '" . $make . "')" );
        } else {

            $GLOBALS [ 'connection' ] -> query (
                    "update vehicleMake set make = '" . $make . "' where reg = '" . $reg . "'" );
        }
    }
    //</editor-fold>
    
    /*
     * dbConnect_update (edit & save)
     * 
     * Vehicle type insert
     * table: vehicleType.
     */
    //<editor-fold defaultstate="collapsed" desc="dbConnect_update :: vehicleMake">
    $vt_result = $GLOBALS [ 'connection' ] -> query (
            "select count(*) from vehicleType where reg = '" . $reg . "'" );

    $vt_counter = 0;

    if ( $vt_result ) {

        while ( $vt_row = $vt_result -> fetch_assoc () ) {

            $vt_counter = $vt_row [ 'count(*)' ];
        }

        $vt_result -> close ();

        if ( $vt_counter == 0 ) {

            $GLOBALS [ 'connection' ] -> query (
                    "insert into vehicleType (reg, type) values ('" . $reg . "', '" . $type . "')" );
        } else {

            $GLOBALS [ 'connection' ] -> query (
                    "update vehicleType set type = '" . $type . "' where reg = '" . $reg . "'" );
        }
    }
    //</editor-fold>

    /*
     * dbConnect_update (edit & save)
     * 
     * We are in the update area but we add/insert a new image, not update an existing one.
     */
    //<editor-fold defaultstate="collapsed" desc="dbConnect_update :: images">
    if ( $imageMime && $imageMime != "no image" ) {

        $GLOBALS [ 'connection' ] -> query (
                "insert into images (mimeText, dataReg) values ('" . $imageMime . "', '" . $reg . "')" );
    }
    //</editor-fold>
    
    /*
     * dbConnect_update (edit & save)
     * 
     * table: data.
     */
    //<editor-fold defaultstate="collapsed" desc="dbConnect_update :: data">
    if ( $id ) {

        $GLOBALS [ 'connection' ] -> query (
                "update data set " .
                "reg = '" . $reg . "', " .
                "date = '" . $date . "', " .
                "fileName = '" . $fileName . "', " .
                "make = '" . $make . "', " .
                "notes = '" . $notes . "', " .
                "location = '" . $location . "' " .
                "where id = " . $id );
    }
    //</editor-fold>

    /*
     * Display GUI feedback info'.
     */
    saveFeedback();

    /*
     * Close the connection.
     */
    $GLOBALS [ 'connection' ] -> close ();
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="dbConnect_get_images">
function dbConnect_get_images () {

    dbConnect();

    if ( filter_input(INPUT_POST, 'reg' ) ) {

        $reg = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'reg' ) );

        $imagesResults = $GLOBALS [ 'connection' ] -> query ( "select mimeText from images where dataReg = '" . $reg . "'" );

        if ( $imagesResults ) {

            $imagesCounter = 0;

            while ( $row = $imagesResults -> fetch_assoc () ) {

                $images [ $imagesCounter ] = $row [ 'mimeText' ];

                $imagesCounter++;
            }

            $imagesResults -> close ();
        }

        foreach ( $images as $i ) {

            echo $i . PHP_EOL;
        }
    }
}
//</editor-fold>

/*
 * Explanation:
 *  
 * Putting $crimeStoppersSearchResults = ...
 * into a separate function seems to lose reference to the Result Set. Whis is
 * why we have repeated the same crimeStoppers query in some of the if 
 * statements (not all). 
 */

//<editor-fold defaultstate="collapsed" desc="dbConnect_search">
function dbConnect_search() {

    dbConnect ();

    $vmView = "vmView_" . rand ( 10,100000 );
    $vtView = "vtView_" . rand ( 10,100000 );
    
    $dataView = "dView_" . rand( 10,100000 );
    $GLOBALS [ 'connection' ] -> query ( "create view " . $dataView . " as select * from data where deleted = 0" );
    
    $cs_searchResults = $GLOBALS [ 'connection' ] -> query ( "SELECT * from crimeStoppers where reported = 0" );

    if ( filter_input ( INPUT_POST, 'reg' ) ) {

        $reg = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'reg' ) );
        
        $searchResults = $GLOBALS [ 'connection' ] -> query ( "SELECT * from " . $dataView . " where reg like '%" . $reg . "%' and deleted = 0" );
    }
    
    else if ( filter_input ( INPUT_POST, 'make' ) ) {
        
        $make = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input( INPUT_POST, 'make' ) );
        
        $GLOBALS [ 'connection' ] -> query ( "create view " . $vmView . " as select reg, make from vehicleMake where make like '%" . $make . "%'" );
        
        $searchResults = $GLOBALS [ 'connection' ] -> query ( "select " . $dataView . ".* from " . $dataView . " inner join " . $vmView . " on " . $dataView . ".reg=" . $vmView . ".reg" );
    } 
    
    else if ( filter_input(INPUT_POST, 'vehicleType' ) ) {
        
        $type = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'vehicleType' ) );
        
        $GLOBALS [ 'connection' ] -> query ( "create view " . $vtView . " as select reg, type from vehicleType where type like '%" . $type . "%'" );
        
        $searchResults = $GLOBALS [ 'connection' ] -> query ( "select " . $dataView . ".* from " . $dataView . " inner join " . $vtView . " on " . $dataView . ".reg=" . $vtView . ".reg" );
    }
    
    else if ( filter_input ( INPUT_POST, 'date' ) ) { 

        $date = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'date' ) );

        $searchResults = $GLOBALS [ 'connection' ] -> query ( "SELECT * from data where date like '%" . $date . "%' and deleted = 0" );
    }
    
    else if ( filter_input ( INPUT_POST, 'fileName' ) ) { 
        
        $fileName = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'fileName' ) );

        $searchResults = $GLOBALS [ 'connection' ] -> query ( "SELECT * from data where fileName like '%" . $fileName . "%' and deleted = 0" );
    } 
    
    else if ( filter_input ( INPUT_POST, 'notes' ) ) {  

        $notes = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'notes' ) );

        $searchResults = $GLOBALS [ 'connection' ] -> query ( "SELECT * from data where notes like '%" . $notes . "%' and deleted = 0" );
    } 
    
    else if ( filter_input ( INPUT_POST, 'location' ) ) {  

        $location = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'location' ) );

        $searchResults = $GLOBALS [ 'connection' ] -> query ( "SELECT * from data where location like '%" . $location . "%' and deleted = 0" );
    } 
    
    else if ( filter_input ( INPUT_POST, 'crimeStoppers' ) ) {  

        /*
         * Easiest way of achieving the required ResultSets for the statements
         * below. I realise that in this case both results will be the same.
         * 
         * We could check whether one of the ResultSets is valid:
         *  
         *   if (!$crimeStoppersSearchResults) ...
         *   else ... use $searchResults instead ...
         * 
         * but it's less typing/maintenance to write this comment.
         */
//        $searchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
        
        $searchResults = $cs_searchResults;
    } 
    else {

        echo "No search criteria!" . PHP_EOL;
    }

    /*
     * TODO: this set of instructions could be a separate function.
     * function foo return an array
     */
    if ( $cs_searchResults ) {

        $cs_counter = 0;

        while ( $row = $cs_searchResults -> fetch_assoc () ) {

            $crimeStoppers [ $cs_counter ] = $row [ 'reg' ];

            $cs_counter++;
        }
        
        $cs_searchResults -> close ();
    }

    if ( $searchResults ) {

        $counter = 0;

        /*
         * We create an array of result strings and then loop through the array,
         * echoing them out.
         * 
         * This array step is unnecessary but I am happy with it at the moment.
         */
        while ($row = $searchResults->fetch_assoc()) {

            $typeResult = $GLOBALS [ 'connection' ] -> query ( "select type from vehicleType where reg = '" . $row [ 'reg' ] . "' limit 1" );

            if ( $typeResult ) {

                while ( $typeRow = $typeResult -> fetch_assoc () ) {

                    $theType = $typeRow [ 'type' ];
                }

                $typeResult -> close ();
            }
              
            $makeResult = $GLOBALS [ 'connection' ] -> query ( "select make from vehicleMake where reg = '" . $row [ 'reg' ] . "' limit 1" );

            if ( $makeResult ) {

                while ( $makeRow = $makeResult -> fetch_assoc () ) {

                    $theMake = $makeRow [ 'make' ];
                }

                $makeResult -> close ();
            }
            
            /*
             * A string that represents a single row of result data.
             */  
            $resultString = "id:" . ( array_key_exists ( "id", $row ) ? $row [ 'id' ] : "-1" ) . ":~:"
                    . "reg:" . $row [ 'reg' ] . ":~:"
                    . "date:" . ( array_key_exists ( "date", $row ) ? $row [ 'date' ] : "foo" ) . ":~:"
                    . "fileName:" . ( array_key_exists ( "fileName", $row ) ? $row [ 'fileName' ] : "None" ) . ":~:"
                    . "make:" . ( ( $theMake ) ? $theMake : "Dunno" ) . ":~:"
                    . "notes:" . ( array_key_exists ( "notes", $row ) ? $row [ 'notes' ] : "Crime Stoppers" ) . ":~:"
                    . "location:" . ( array_key_exists ( "location", $row ) ? $row [ 'location' ] : "Dunno" ) . ":~:"
                    . "type:" . ( ( $theType ) ? $theType : "Dunno" ) . ":~:"
                    . "crimeStoppers:" . ( in_array ( $row [ 'reg' ], $crimeStoppers ) ? "0" : "1" );
            
            /*
             * We add that string to an array.
             */
            $allResultStrings [ $counter ] = $resultString;

            /*
             * Increment the array counter.
             */
            $counter++;
        }

        /*
         * Close the result set.
         */
        $searchResults -> close ();
            
        $GLOBALS [ 'connection' ] -> query ( "drop view " . $vmView );
        $GLOBALS [ 'connection' ] -> query ( "drop view " . $vtView );
        $GLOBALS [ 'connection' ] -> query ( "drop view " . $dataView );
        
        /*
         * echo all of the result strings.
         * 
         * Because the results are now held in an array, we could do additional 
         * things with them.
         */
        foreach ( $allResultStrings as $x ) {

            echo $x . PHP_EOL;
        }
    } else {

        echo "Search error " . PHP_EOL;
    }

    $GLOBALS [ 'connection' ] -> close ();
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="dbConnect_addNewEntry">
function dbConnect_addNewEntry () {

    dbConnect ();

    /*
     * Seems we only get a return value if the query was successful.
     * For examples replace field1 with field2 and no value is returned.               
     */

    /*
     * Values received from the URL.
     */
    //<editor-fold defaultstate="collapsed" desc="values from URL">
    $reg = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'reg' ) );
    $make = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'make' ) );
    $date = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'date' ) );
    $location = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'location' ) );
    $fileName = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'fileName' ) );
    $notes = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'notes' ) );
    $crimeStoppers = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'crimeStoppers' ) );
    $imageMime = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'imageMime' ) );
    $vehicleType = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'vehicleType' ) );
    //</editor-fold>
    /*
     * End values received.
     */

    /*
     * crimeStoppers insert 
     * table: crimeStoppers
     * 
     * Get the number of crimeStopper entries that match the reg.
     */
    //<editor-fold defaultstate="collapsed" desc="crimeStoppers results">
    $cs_result = $GLOBALS [ 'connection' ] -> query (
            "select count(*) from crimeStoppers where reg = '" . $reg . "'" );

    $cs_counter = 0;

    if ( $cs_result ) {

        while ( $cs_row = $cs_result -> fetch_assoc () ) {

            $cs_counter = $cs_row [ 'count(*)' ];
        }

        $cs_result -> close ();
    }
    //</editor-fold>
    
    /*
     * Registration was not found in crimeStoppers table
     * so insert it.
     */
    //<editor-fold defaultstate="collapsed" desc="insert into crimeStoppers">
    if ( $cs_counter == 0 ) {

        $GLOBALS [ 'connection' ] -> query (
                "insert into crimeStoppers (reg, reported) values ('" . $reg . "', " . $crimeStoppers . ")" );
    } 
    /*
     * Registration was found in crimeStoppers table
     * so update.
     */
    else {

        $GLOBALS [ 'connection' ] -> query (
                "update crimeStoppers set reported = " . $crimeStoppers . " where reg = '" . $reg . "'" );
    }
    //</editor-fold>
    /*
     * End crimeStoppers insert.
     */

    /*
     * Vehicle Type.
     */
    //<editor-fold defaultstate="collapsed" desc="vehicleType results">
    $vt_result = $GLOBALS [ 'connection' ] -> query (
            "select count(*) from vehicleType where reg = '" . $reg . "'" );
    
    $vt_counter = 0;
    
    if ( $vt_result ) {

        while ( $vt_row = $vt_result -> fetch_assoc () ) {

            $vt_counter = $vt_row [ 'count(*)' ];
        }

        $vt_result -> close ();

        if ($vt_counter == 0) {

            $GLOBALS [ 'connection' ] -> query (
                    "insert into vehicleType (reg, type) values ('" . $reg . "', '" . $vehicleType . "')" );
        } else {

            $GLOBALS [ 'connection' ] -> query (
                    "update vehicleType set type = '" . $vehicleType . "' where reg = '" . $reg . "'" );
        }
    }
    //</editor-fold>
    /*
     * End vehicleType
     */
    
    /*
     * Vehicle make insert
     * table: vehicleMake.
     */
    //<editor-fold defaultstate="collapsed" desc="vehicleMake results">
    $vm_result = $GLOBALS [ 'connection' ] -> query (
            "select count(*) from vehicleMake where reg = '" . $reg . "'" );

    $vm_counter = 0;

    if ( $vm_result ) {

        while ( $vm_row = $vm_result -> fetch_assoc () ) {

            $vm_counter = $vm_row [ 'count(*)' ];
        }

        $vm_result -> close ();
 
        if ( $vm_counter == 0 ) {

            $GLOBALS [ 'connection' ] -> query (
                    "insert into vehicleMake (reg, make) values ('" . $reg . "', '" . $make . "')" );
        } else {

            $GLOBALS [ 'connection' ] -> query (
                    "update vehicleMake set make = '" . $make . "' where reg = '" . $reg . "'" );
        }
    }
    //</editor-fold>
    /*
     * End vehicleMake insert.
     */

    /*
     * Add a new image.
     */
    //<editor-fold defaultstate="collapsed" desc="insert into images">
    if ( $imageMime 
            && $imageMime != "no image" ) {

        $GLOBALS [ 'connection' ] -> query (
                "insert into images (mimeText, dataReg) values ('" . $imageMime . "', '" . $reg . "')" );
    }
    //</editor-fold>

    /*
     * data insert (table is called: data).
     */
    //<editor-fold defaultstate="collapsed" desc="insert into data">
    $GLOBALS [ 'connection' ] -> query (
            "insert into data(reg, make, date, location, fileName, notes) values ("
            . "'" . $reg . "', "
            . "'" . $make . "', "
            . "'" . $date . "', "
            . "'" . $location . "', "
            . "'" . $fileName . "', "
            . "'" . $notes . "')" );
    //</editor-fold>

    saveFeedback ();

    $GLOBALS [ 'connection' ] -> close ();
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="deletedFeedback">
function deletedFeedback () {

    /*
     * We just deleted this id by setting its deleted value to: 0.
     */
    if ( filter_input ( INPUT_POST, 'id' ) ) {

        echo "id:" . ( filter_input ( INPUT_POST, 'id' ) ) . PHP_EOL;
    }

    /*
     * Get count of entries not deleted.
     */
    $result = $GLOBALS [ 'connection' ] -> query ( "SELECT count(*) from data where deleted = 0" );

    if ( $result ) {

        while ( $row = $result -> fetch_assoc () ) {

            echo "count:" . $row [ 'count(*)' ] . PHP_EOL;
        }

        $result -> close ();
    }
    /*
     * End of count.
     */
}
//</editor-fold>

/*
 * Gui feedback
 */

//<editor-fold defaultstate="collapsed" desc="saveFeedback">
function saveFeedback () {

    /*
     * We just edited and updated an entry.
     */
    if ( filter_input ( INPUT_POST, 'id' ) ) {

        echo "id:" . ( filter_input ( INPUT_POST, 'id' ) ) . PHP_EOL;
    }
    /*
     * New entry / insert.
     */ else {

        echo "id:" . $GLOBALS [ 'connection' ] -> insert_id . PHP_EOL;
    }

    /*
     * Get number of entries with this registrations.
     */
    $result = $GLOBALS [ 'connection' ] -> query (
            "SELECT count(*) from data where reg = '" . filter_input ( INPUT_POST, 'reg' ) . "' and deleted = 0" );

    if ( $result ) {

        while ( $row = $result -> fetch_assoc () ) {

            echo "duplicates:" . $row [ 'count(*)' ] . PHP_EOL;
        }

        $result -> close ();
    }
    /*
     * End get number of duplicate registrations.
     */

    /*
     * Get total number of entries that are not marked as deleted.
     */
    $result2 = $GLOBALS [ 'connection' ] -> query ( "SELECT count(*) from data where deleted = 0" );

    if ( $result2 ) {

        while ( $row = $result2 -> fetch_assoc () ) {

            echo "count:" . $row [ 'count(*)' ] . PHP_EOL;
        }

        $result2 -> close ();
    }
    /*
     * End total number of entries.
     */
}
//</editor-fold>

//<editor-fold defaultstate="collapsed" desc="dbConnect_updateGlobalNotes">
function dbConnect_updateGlobalNotes () {

    dbConnect ();

    $notes = mysqli_real_escape_string ( $GLOBALS [ 'connection' ], filter_input ( INPUT_POST, 'notes' ) );

    $notesResult = $GLOBALS [ 'connection' ] -> query ( "select count(*) from globalNotes" );

    $notesCount = 0;

    if ( $notesResult ) {

        while ( $row = $notesResult -> fetch_assoc () ) {

            $notesCount = $row [ 'count(*)' ];
        }

        $notesResult -> close ();
    }

    if ( $notesCount == 0 ) {

        $GLOBALS [ 'connection' ] -> query ( "insert into globalNotes (notes) values ('" . $notes . "')" );
    } else {

        $GLOBALS [ 'connection' ] -> query ( "update globalNotes set notes = '" . $notes . "'" );
    }                        

    $GLOBALS [ 'connection' ] -> close ();
}
//</editor-fold>                                

//<editor-fold defaultstate="collapsed" desc="dbConnect_getGlobalNotes">
function dbConnect_getGlobalNotes() {

    dbConnect();

    $notesResult = $GLOBALS [ 'connection' ] -> query ( "select count(*) from globalNotes" );

    $notesCount = 0;

    if ( $notesResult ) {

        while ( $row = $notesResult -> fetch_assoc () ) {

            $notesCount = $row [ 'count(*)' ];
        }

        $notesResult -> close ();
    }

    if ( $notesCount == 0 ) {

        echo 'empty notes' . PHP_EOL;
    } 
    else {

        $notesResult_2 = $GLOBALS [ 'connection' ] -> query ( "select * from globalNotes" );

        while ( $row = $notesResult_2 -> fetch_assoc () ) {

            echo $row [ 'notes' ] . PHP_EOL;
        }

        $notesResult_2 -> close ();
    }

    $GLOBALS [ 'connection' ] -> close ();
}
//</editor-fold>

//<editor-fold desc="Stuff for reference." defaultstate="collapsed">

//    echo $mysqli->host_info . PHP_EOL;
    
//</editor-fold>


