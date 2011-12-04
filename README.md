PHP Framework for a REST API
===

## To use:
1) Download
--
2) Change database parameters
--
3) open 'get.class.php' [this contains the GET functions]
--
  - place custom functions in the API_FUNCTIONS class [use lowercase]
  - update $valid_functions
4) repeat with 'post.class.php' [this contains the POST functions]
--


### Functions you'll want to use

setRequiredParams
    Param 1: Accepts a string, or an array of strings. 
    Ensures that parameters are available
    Returns the required variables [either just a straight var, or an array with list()]

respond
    Param 1: status code [200 on success]
    Param 2: array of data, or a string message if its an error
    Responds to the client, and ends the script

getParam
    Param 1: the key of the parameter
    Return the value of the element asked for	

getData
    Return an array of all the data [either $_POST or $_GET]

query
    Param 1: the mysql query
    executes query and returns $result after error checking

getMethod
    returns the method that the client wants executed