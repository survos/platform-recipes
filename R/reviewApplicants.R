##################################################
# Run this test script with: Rscript reviewApplicants.R 
# This will output errors, comments and progress
##################################################

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

# Login. Be sure to use correct credentials in parameters.R
loginSurvos(username, password)

# Return applicants data. maxPerPage defaults to 25
applicants <- applicants(maxPerPage = "250")

# Drop all rows with age NA
applicants <- applicants[complete.cases(applicants[,6]),]

# Filter for ages >= 21 & <= 34
applicants <- dplyr::filter(applicants, applicants$age >= 21, applicants$age <= 34)

