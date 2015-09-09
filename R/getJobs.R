if(!require("devtools")){
  # If devtools fails with zero exit status due to unable to install xml2 library, 
  # check libxml2-dev is installed on local machine
  install.packages("devtools")
  library("devtools")
  devtools::install_github("survos/platform-api-r")
  library("survos")

} else {
  library("devtools")
  devtools::install_github("survos/platform-api-r")
  library("survos")
  }

library("RCurl")
library("jsonlite")
library("httr")

loginSurvos(username="YourUsername", password="YourPassword")

jobsOut <- jobs(projectCode="nyu_demo")

# A list of all job ids for project code "nyu_demo" now in this variable
jobsOut$id
