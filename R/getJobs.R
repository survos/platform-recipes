##########################################################################
# Jobs, Users, Assignments & Locations Endpoint Example
# Run this test script with: Rscript getJobs.R 
# This will output errors, comments and progress
##########################################################################

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

# Load additional libraries. 
# These should all have been installed with the survos package if not previously.
library("RCurl")
library("jsonlite")
library("httr")
library("plyr")
library("dplyr")

# Load external file containing username, password and API endpoint data. 
# File must be saved in active working directory. See parameters.R.dist for example format
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
# If you put something like XYZ in here instead of a number, 
# the script will stop and throw an error to check URL validity
assignments <- assignments(163)

# Write a nice little csv to check output 
write.csv(jobsOut$id, file = "jobsOut.csv")

# Test Locations. This will return a lot of data! Check username and password in parameters.R
locations <- locations(projectCode="truth_posse", memberId="5380", maxPerPage="100")

# Also look for getJobs.Rout file if running from command line with R CMD BATCH. 
# This will show result of running script and any errors.
