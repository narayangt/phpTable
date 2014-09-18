phpTable
========

Php code that handle creating and updating table values

This project fulfill different aspects of PHP and MYSQL database integration in form of following classes

1. maindb_con
    This is a class that holds the credintal informations of database in PHPMYAdmin and rest codes uses this class to get connection to the database.
2. table
  This class defines different method for different purpose of a table in database and child classes can easily perform gread job in couple of lines because of this class. Some of this useful methods are as follow,


      getPrimeryKey(): return primery key name or false if doesnt exists.
      
      createTableIfNotExists(): returns query to create table into database or false if no records found
      
      valueAsJsonString(): values of records as json string
      
      searchAndUpdateValues($search): returns true if search value in $search array found in table followed retriving rest value
      and update in class or false if records not found according to value in $search value.
      
      insertAndUpdateValues(): insert values as new record and update new values in record like primery key value and dates etc.
      
      insert(): returns query to insert values in class records into database table or false if records not found
      
      scanSubmittedForm($searchOrNot): scan submitted form and update and find remaining values from database according to boolean value in $searchOrNot.
      
      __toString(): display structure, values and error occured if any during the process





scTableImplementation.php is our final result that derives 2 more classes from table class and populate tables into database. 

provided pattern can be used in any project and works just fine. I will be adding more methods to add more functionality like printing records in HTML and send email etc. I need more support and motivation from you all ........

