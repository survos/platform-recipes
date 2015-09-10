# Check for the availability of devtools. If not installed, do so
if(!require("devtools")){
  # If devtools fails with zero exit status due to unable to install xml2 library, 
  # check libxml2-dev is installed on local machine
  install.packages("devtools")
  library("devtools")
  devtools::install_github("survos/platform-api-r")
  library("survos")
 
# Force install newest version of survos package.
} else {
  library("devtools")
  devtools::install_github("survos/platform-api-r")
  library("survos")
}

# Load additional libraries. These should all have been installed with the survos package if not previously.
library("RCurl")
library("jsonlite")
library("httr")
library("dplyr")

# Load external file containing username, password and API endpoint data. File must be saved in active working directory
source("parameters.R")

# Login
loginSurvos(username, password)

# Extract jobs data
jobsOut <- jobs(projectCode="nyu_demo")

# A list of all job ids for project code "nyu_demo" now in this variable
jobsOut$id

# A data frame containing all user data, from across 29 pages
users <- users()

# Returns all assignment data associated with job_id 163
assignments <- assignments(163)
