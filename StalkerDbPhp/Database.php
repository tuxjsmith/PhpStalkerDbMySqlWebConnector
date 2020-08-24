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
 * Adds a 'connection' key to the built in associate array $GLOBALS
 */
global $connection;

/*
 * Make a connection to the database.
 */
function dbConnect () {

    $GLOBALS['connection'] = new mysqli("localhost", "testUser", "foobar", "followingCars");

    if ($GLOBALS['connection']->connect_errno) {

        echo "[1] Failed to connect to MySQL: (" . $GLOBALS['connection']->connect_errno . ") " . $GLOBALS['connection']->connect_error;
    
        echo PHP_EOL;
        
        exit;
    }
    
    if (!$GLOBALS['connection']->set_charset('utf8')) {
    
        echo "Error loading character set utf8: %s\n", $GLOBALS['connection']->error;
        
        echo PHP_EOL;
        
        exit;
    }
}

/*
 * 
 */
function dbConnect_setDeleted() {

    dbConnect ();

    $id = mysqli_real_escape_string($GLOBALS['connection'], $_POST["id"]);

    $GLOBALS['connection']->query(
            "update data " .
            "set deleted = 1 " .
            "where id = " . $id);

    deletedFeedback ();    
    
    $GLOBALS['connection']->close();
}

function dbConnect_update() {

    dbConnect();

    /*
     * URL received parameters.
     * 
     * Variables: $reg, $make, etc
     * are retrieved from the URL post ($_POST) values.
     */
    $reg = mysqli_real_escape_string($GLOBALS['connection'], $_POST["reg"]);
    $make = mysqli_real_escape_string($GLOBALS['connection'], $_POST["make"]);
    $date = mysqli_real_escape_string($GLOBALS['connection'], $_POST["date"]);        
    $location = mysqli_real_escape_string($GLOBALS['connection'], $_POST["location"]);            
    $fileName = mysqli_real_escape_string($GLOBALS['connection'], $_POST["fileName"]);       
    $notes = mysqli_real_escape_string($GLOBALS['connection'], $_POST["notes"]);
    $id = mysqli_real_escape_string($GLOBALS['connection'], $_POST["id"]);
    $crimeStoppers = mysqli_real_escape_string($GLOBALS['connection'], $_POST["crimeStoppers"]);
    $imageMime = mysqli_real_escape_string($GLOBALS['connection'], $_POST["imageMime"]);
    
    /*
     * A mechanism for adding CrimeStopper data.
     * 
     * - Get a count of matching CrimeStopper registrations.
     */
    $crimeStoppersResult = $GLOBALS['connection']->query(
            "select count(*) from crimeStoppers where reg = '" . $_POST["reg"] . "'"
            );
    
    $regCounter = 0;
    
    if ($crimeStoppersResult) {

        while ($row = $crimeStoppersResult->fetch_assoc()) {

            $regCounter = $row['count(*)'];
        }
        
        $crimeStoppersResult->close();
    }
    
    /*
     * If no entries match the reg' in crimeStoppers table then insert a new record.
     */
    if ($regCounter == 0) {
    
        $GLOBALS['connection']->query(
                "insert into crimeStoppers (reg, reported) "
                . "values ('" . $reg . "', " . $crimeStoppers . ")");
    }
    /*
     * If count is > 0 then update the record that matches the reg'.
     */
    else {
        
        $GLOBALS['connection']->query(
            "update crimeStoppers set " .
            "reported = " . $crimeStoppers .
                " where reg = '" . $reg . "'");
    }
    
    /*
     * We are in the update area but we add a new image, not update an existing one.
     */
    if ($imageMime 
        && $imageMime != "no image") {
        
        $GLOBALS['connection']->query(
            "insert into images (mimeText, dataReg) "
                . "values ('" . $imageMime . "', '" . $reg . "')");
        
    }
    
    /*
     * Update (db table) data.
     */
    if ($id) {
        
        $GLOBALS['connection']->query(
                "update data set " .
                "reg = '" . $reg . "', " .
                "date = '" . $date . "', " .
                "fileName = '" . $fileName . "', " .
                "make = '" . $make . "', " .
                "notes = '" . $notes . "', " .
                "location = '" . $location . "' " .
                "where id = " . $id);
    }
    
    /*
     * Display GUI feedback info'.
     */
    saveFeedback();
    
    /*
     * Close the connection.
     */
    $GLOBALS['connection']->close();
}

function dbConnect_get_images() {

    dbConnect();

    if ($_POST["reg"]) {

        $reg = mysqli_real_escape_string($GLOBALS['connection'], $_POST["reg"]);

        $imagesResults = $GLOBALS['connection']->query("select mimeText from images where dataReg = '" . $reg . "'");

        if ($imagesResults) {

            $imagesCounter = 0;

            while ($row = $imagesResults->fetch_assoc()) {

                $images[$imagesCounter] = $row['mimeText'];
                
                $imagesCounter++;
            }

            $imagesResults->close();
        }

        foreach ($images as $i) {

            echo $i . PHP_EOL;
        }
    }
}

/*
 * Explanation:
 *  
 * Putting $crimeStoppersSearchResults = ...
 * into a separate function seems to lose reference to the Result Set. Whis is
 * why we have repeated the same crimeStoppers query in some of the if 
 * statements (not all). 
 */
function dbConnect_search () {

    dbConnect();
    
    $commonCrimeStopperStatement = "SELECT * from crimeStoppers where reported = 0";
        
    if ($_POST["reg"]) {
    
        $reg = mysqli_real_escape_string($GLOBALS['connection'], $_POST["reg"]);
        
        $searchResults = $GLOBALS['connection']->query("SELECT * from data where reg like '%". $reg . "%' and deleted = 0");
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else if ($_POST["make"]) {
    
        $make = mysqli_real_escape_string($GLOBALS['connection'], $_POST["make"]);
        
        $searchResults = $GLOBALS['connection']->query("SELECT * from data where make like '%". $make . "%' and deleted = 0");
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else if ($_POST["date"]) {
        
        $date = mysqli_real_escape_string($GLOBALS['connection'], $_POST["date"]); 
    
        $searchResults = $GLOBALS['connection']->query("SELECT * from data where date like '%". $date . "%' and deleted = 0");
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else if ($_POST["fileName"]) {
    
        $fileName = mysqli_real_escape_string($GLOBALS['connection'], $_POST["fileName"]);
        
        $searchResults = $GLOBALS['connection']->query("SELECT * from data where fileName like '%". $fileName . "%' and deleted = 0");
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else if ($_POST["notes"]) {
        
        $notes = mysqli_real_escape_string($GLOBALS['connection'], $_POST["notes"]);  
    
        $searchResults = $GLOBALS['connection']->query("SELECT * from data where notes like '%". $notes . "%' and deleted = 0");
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else if ($_POST["location"]) {
    
        $location = mysqli_real_escape_string($GLOBALS['connection'], $_POST["location"]);  
        
        $searchResults = $GLOBALS['connection']->query("SELECT * from data where location like '%" . $location . "%' and deleted = 0");
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else if ($_POST["crimeStoppers"]) {
    
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
        $searchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
        
        $crimeStoppersSearchResults = $GLOBALS['connection']->query($commonCrimeStopperStatement);
    }
    else {
        
        echo "No search criteria!\n";
    }
    
    if ($crimeStoppersSearchResults) {
        
        $crimeStoppersCounter = 0;
        
        while ($row = $crimeStoppersSearchResults->fetch_assoc()) {
            
            $crimeStoppers[$crimeStoppersCounter] = $row['reg'];
            
            $crimeStoppersCounter++;
        }
    }
    
    $crimeStoppersSearchResults->close ();

    if ($searchResults) {

        $counter = 0;
        
        /*
         * We create an array of result strings and then loop through the array,
         * echoing them out.
         * 
         * This array step is unnecessary but I am happy with it at the moment.
         */
        while ($row = $searchResults->fetch_assoc()) {

            /*
             * A string that represents a single row of result data.
             */
            $resultString = "id:" . ( array_key_exists("id", $row) ? $row['id'] : "-1") . ":~:"
                        . "reg:"  . $row['reg'] . ":~:"
                        . "date:" . ( array_key_exists("date", $row) ? $row['date'] : "foo") . ":~:"
                        . "fileName:" . ( array_key_exists("fileName", $row) ? $row['fileName'] : "None") . ":~:"
                        . "make:" . ( array_key_exists("make", $row) ? $row['make'] : "Dunno") . ":~:"
                        . "notes:" . ( array_key_exists("notes", $row) ? $row['notes'] : "Crime Stoppers" ) . ":~:"
                        . "location:" . ( array_key_exists("location", $row) ? $row['location'] : "Dunno" ) . ":~:"
                        . "crimeStoppers:" . ( in_array($row['reg'], $crimeStoppers) ? "0" : "1" ); 
            
            /*
             * We add that string to an array.
             */
            $allResultStrings[$counter] = $resultString;
                     
            /*
             * Increment the array counter.
             */
            $counter++;
        }
        
        /*
         * Close the result set.
         */
        $searchResults->close();
        
        /*
         * echo all of the result strings.
         * 
         * Because the results are now held in an array, we could do additional 
         * things with them.
         */
        foreach ($allResultStrings as $x) {
            
            echo $x . PHP_EOL;
        }
    }
    else {
        
        echo "Search error ";
    }
    
    $GLOBALS['connection']->close ();
}

function dbConnect_addNewEntry() {

    dbConnect();
    
    /*
     * Seems we only get a return value if the query was successful.
     * For examples replace field1 with field2 and no value is returned.               
     */
    
    /*
     * Values received from the URL.
     */
    $reg = mysqli_real_escape_string($GLOBALS['connection'], $_POST["reg"]);
    $make = mysqli_real_escape_string($GLOBALS['connection'], $_POST["make"]);
    $date = mysqli_real_escape_string($GLOBALS['connection'], $_POST["date"]);        
    $location = mysqli_real_escape_string($GLOBALS['connection'], $_POST["location"]);            
    $fileName = mysqli_real_escape_string($GLOBALS['connection'], $_POST["fileName"]);       
    $notes = mysqli_real_escape_string($GLOBALS['connection'], $_POST["notes"]);
    $crimeStoppers = mysqli_real_escape_string($GLOBALS['connection'], $_POST["crimeStoppers"]);
    $imageMime = mysqli_real_escape_string($GLOBALS['connection'], $_POST["imageMime"]);
    /*
     * End values received.
     */
    
    /*
     * crimeStoppers insert (table is called: crimeStoppers).
     */
    $result_1 = $GLOBALS['connection']->query("select count(*) from crimeStoppers where reg = '" . $reg . "'");
    
    $regCounter = 0;
    
    if ($result_1) {

        while ($row = $result_1->fetch_assoc()) {

            $regCounter = $row['count(*)'];
        }
        
        $result_1->close();
    }
    
    if ($regCounter == 0) {
    
        $GLOBALS['connection']->query("insert into crimeStoppers (reg, reported) values ("
                . "'" . $reg . "', " 
                . $crimeStoppers . ")");
    }
    else {
        
        $GLOBALS['connection']->query(
            "update crimeStoppers set " .
            "reported = " . $crimeStoppers .
                " where reg = '" . $reg . "'");
    }
    /*
     * End crimeStoppers insert.
     */
    
    /*
     * Add a new image.
     */
    if ($imageMime 
        && $imageMime != "no image") {
        
        $GLOBALS['connection']->query(
            "insert into images (mimeText, dataReg) "
                . "values ('" . $imageMime . "', '" . $reg . "')");
        
    }
    
    /*
     * data insert (table is called: data).
     */
    $GLOBALS['connection']->query("insert into data(reg, make, date, location, fileName, notes) values ("
            . "'" . $reg ."', "
            . "'" . $make . "', "
            . "'" . $date . "', "
            . "'" . $location . "', "
            . "'" . $fileName . "', "
            . "'" . $notes . "')");
     
    saveFeedback();
    
    $GLOBALS['connection']->close ();
}

function deletedFeedback () {
    
    /*
     * We just deleted this id by setting its deleted value to: 0.
     */
    if ($_POST["id"]) {
    
        echo "id:" . ($_POST["id"]) . PHP_EOL;
    }
    
    /*
     * Get count of entries not deleted.
     */
    $result = $GLOBALS['connection']->query("SELECT count(*) from data where deleted = 0");

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            echo "count:" . $row['count(*)'] . PHP_EOL;
        }
        
        $result->close();
    }
    /*
     * End of count.
     */
}

/*
 * Gui feedback
 */
function saveFeedback () {
    
    /*
     * We just edited and updated an entry.
     */
    if ($_POST["id"]) {
    
        echo "id:" . ($_POST["id"])  . PHP_EOL;
    }
    /*
     * New entry / insert.
     */
    else {
        
        echo "id:" . $GLOBALS['connection']->insert_id . PHP_EOL;
    }
    
    /*
     * Get number of entries with this registrations.
     */
    $result = $GLOBALS['connection']->query(
            "SELECT count(*) from data where reg = '" . $_POST["reg"] . "' and deleted = 0"
            );

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            echo "duplicates:" . $row['count(*)'] . PHP_EOL;
        }
        
        $result->close();
    }
    /*
     * End get number of duplicate registrations.
     */
    
    /*
     * Get total number of entries that are not marked as deleted.
     */
    $result2 = $GLOBALS['connection']->query("SELECT count(*) from data where deleted = 0");

    if ($result2) {

        while ($row = $result2->fetch_assoc()) {

            echo "count:" . $row['count(*)'] . PHP_EOL; 
        }
        
        $result2->close();
    }
    /*
     * End total number of entries.
     */
}

function dbConnect_updateGlobalNotes () {
    
    dbConnect();
    
    $notes = mysqli_real_escape_string($GLOBALS['connection'], $_POST["notes"]);
    
    $notesResult = $GLOBALS['connection']->query("select count(*) from globalNotes");
    
    $notesCount = 0;
    
    if ($notesResult) {

        while ($row = $notesResult->fetch_assoc()) {

            $notesCount = $row['count(*)'];
        }
        
        $notesResult->close();
    }
    
    if ($notesCount == 0) {
    
        $GLOBALS['connection']->query(
                "insert into globalNotes (notes) "
                . "values ('" . $notes . "')");
    }
    else {
        
        $GLOBALS['connection']->query(
            "update globalNotes set " .
            "notes = '" . $notes . "'");
    }
    
    $GLOBALS['connection']->close ();
}

function dbConnect_getGlobalNotes () {
    
    dbConnect();
    
    $notesResult = $GLOBALS['connection']->query("select count(*) from globalNotes");
    
    $notesCount = 0;
    
    if ($notesResult) {

        while ($row = $notesResult->fetch_assoc()) {

            $notesCount = $row['count(*)'];
        }
        
        $notesResult->close();
    }
    
    if ($notesCount == 0) {
    
        echo 'empty notes' . PHP_EOL;
    }
    else {
        
        $notesResult_2 = $GLOBALS['connection']->query(
            "select * from globalNotes");
        
        while ($row = $notesResult_2->fetch_assoc()) {

            echo $row['notes'] . PHP_EOL;
        }
        
        $notesResult_2->close();
    }
    
    $GLOBALS['connection']->close ();
}

//<editor-fold desc="Stuff for reference." defaultstate="collapsed">

//    echo $mysqli->host_info . PHP_EOL;
    
//</editor-fold>


