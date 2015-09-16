##########################################################################
# Members Endpoint Example
# Run this test script with: Rscript getMembers.R 
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

# Returns all data from members endpoint for project code "demo".
# Also supported are "maxPerPage" and "pii" calls. maxPerPage defaults to 25. pii defaults to 0.
# Code below will need to change if pii set to 1, to account for new nested list.
members <- members(projectCode="demo")

# Data is returned as a list with two nested dataframes, both with and without personal data fields. 
# Extract both lists to new data frames. 
membersNoPersonalData <- as.data.frame(members[1])
membersWithPersonalData <- as.data.frame(members[2])

# Insert empty age and zip cols into dataframe with no personal data. Then remove empty nested personal_data list.
age <- NA
zip <- NA
membersNoPersonalData <- dplyr:: mutate(membersNoPersonalData, age, zip)
membersNoPersonalData <- dplyr::select(membersNoPersonalData, everything(), -personal_data)

# Create a third dataframe extracting nested age and zip data from data frame which contains it.
justPersonalData <- data.frame(membersWithPersonalData$personal_data)

# Remove now superfluous personal_data column
membersWithPersonalData <- dplyr::select(membersWithPersonalData, everything(), -personal_data)

# Bind matching dataframe with age/zip data
membersWithPersonalData <- dplyr::bind_cols(membersWithPersonalData, justPersonalData)

# Bind both the With and Without Personal Data dataframes for All Members
allMembers <- dplyr::bind_rows(membersNoPersonalData , membersWithPersonalData)

# Filter for just Applicants
justApplicants <- dplyr::filter(allMembers, allMembers$enrollment_status_code == "applicant")

# Filter for just Applicants between the ages of 21 and 34
rightAge <- dplyr::filter(justApplicants, justApplicants$age >= "21" & justApplicants$age <= "34")

cat("Script complete. Applicants data, filtered by age >= 21 and <= 34,  now held in a variable called 'rightAge'")