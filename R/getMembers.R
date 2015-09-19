##########################################################################
# Members Endpoint Example
# Run this test script with the following arguments: 
# Rscript getMembers.R --username yourusername --password yourpassword --endpoint https://endpointtouse.survos.com/app_dev.php/api1.0/
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
  suppressWarnings(library("survos"))
}

# Keep it clean
suppressMessages({
  
# Load additional libraries. 
# These should all have been installed with the survos package if not previously.
library("RCurl")
library("jsonlite")
library("httr")
library("plyr")
library("dplyr")
library("argparser")
library("knitr")

})


# Login
# Load external file containing username, password and API endpoint data. 
# File must be saved in active working directory. See parameters.R.dist for example format
source("parameters.R")

loginSurvos(username, password)

# Add arguments which may be passed from the command line to override values held in parameters.
  
gM <- arg_parser("Survos API Login Details")
gM <- add_argument(gM, c("--username", "--password", "--endpoint"), help = c("Your Survos API username","Your Survos API password", "The Survos API endpoint to use"))

argv <- parse_args(gM)

# Check if anything has been passed from the command line.
if(!is.na(argv$username)){
  
username <- argv$username
password <- argv$password
endPoint <- argv$endpoint
loginSurvos(username, password)

}

# Returns all data from members endpoint for project code "demo".
# Also supported are "maxPerPage" and "pii" calls. maxPerPage defaults to 25. pii defaults to 0.
# Code below will need to change if pii set to 1, to account for new nested list.
members <- members(projectCode="demo")

# Data is returned as a list with potentially multiple nested dataframes, with age and zip in another nested list
# This ugly code extracts all of that and makes a nice single dataframe
# Remove the personal_data from each nested dataframe
membersMinusPD <- lapply(members, function(x) dplyr::select(x, -personal_data))

# Extract just the age information for each double nested list
membersAge <- lapply(members, function(x) lapply(x$personal_data, '[[', 'age'))
membersAge <- lapply(membersAge, function(x) lapply(x, function(y) ifelse(is.null(y), NA, y)))
membersAge <- lapply(membersAge, function(x) unlist(x))
membersAge <- lapply(membersAge, function(x) as.data.frame(x))

# Extract just the zip information for each double nested list
membersZip <- lapply(members, function(x) lapply(x$personal_data, '[[', 'zip'))
membersZip <- lapply(membersZip, function(x) lapply(x, function(y) ifelse(is.null(y), NA, y)))
membersZip <- lapply(membersZip, function(x) unlist(x))
membersZip <- lapply(membersZip, function(x) as.data.frame(x))

# Make each list into its own dataframe and rename cols
membersMinusPD <- ldply(membersMinusPD, data.frame)
membersAge <- ldply(membersAge, data.frame)
membersAge <- rename(membersAge, age = x)
membersZip <- ldply(membersZip, data.frame)
membersZip <- rename(membersZip, zip = x)

# Combine them all into one dataframe
allMembers <- dplyr::bind_cols(membersMinusPD, membersAge, membersZip)

# Filter for just Applicants
justApplicants <- dplyr::filter(allMembers, allMembers$enrollment_status_code == "applicant")

# Filter for just Applicants between the ages of 21 and 34
rightAge <- dplyr::filter(justApplicants, justApplicants$age >= "21" & justApplicants$age <= "34")

# Output some comments about the script completing and a pretty table.
cat("Script complete. Applicants data, filtered by age >= 21 and <= 34,  now held in a variable called 'rightAge' and printed below")
print(kable(rightAge))

# Ask some questions about which applicants to accept or reject
lapply(rightAge$id, function(x){
  
cat("Would you like to accept or reject id:",x,"? reject/accept\n", sep="")

actionIn <- readLines(file("stdin"),1)

cat("Please type a comment for internal use:\n")

commentIn <- readLines(file("stdin"),1)

cat("Please type a message for the applicant:\n")

messageIn <- readLines(file("stdin"),1)

applicantsAction(action = actionIn, id = x, comment = commentIn, message = messageIn)

})


