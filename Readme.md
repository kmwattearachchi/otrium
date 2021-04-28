##About the application
``This application allows you to generate a report based on the given date period. 
The parameters can be changed form "Constants.php" file.``

``To change the start and end date, please refer to the above config file.``

###`How to run`
`Create a DB "otrium" and import the otrium_db.sql`

`run composer install`

`Execute the below command in the terminal`
#####`php index.php`

###About the generated report
`Once the above command executed in using the command line, two files will be created under the ""Reports" folder . `

`Seperate folders will be generated for each day and inside each day, there are two reports.`

`For a better user experience its recomended to use microsoft excel / OpenOffice Calc or Libre Office to open the file.`

###`How to run test suit`

`./vendor/bin/phpunit tests`