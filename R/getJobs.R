firstTime <- 1
if (firstTime) {
  install.packages('devtools')
  devtools::install_github("survos/platform-api-r")
}

library("survos")

loginSurvos(username = "YourUserNameHere", password = "YourPasswordHere", style = "POST")

jobsFrame <- jobs(projectCode = "nyu_demo", page = "1")