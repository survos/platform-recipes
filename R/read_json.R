# install.packages('jsonlite')
# install.packages('lubridate')
# library(jsonlite)
# library(lubridate)
# library(plyr)
# 
# ######################
# # read data with API #
# ######################
# 
# # segments
# baseurl <- "http://streetopedia.com/api/segments?_format=json"
# pages <- list()
# for(i in 1:10){
#   mydata <- fromJSON(paste0(baseurl, "&page=", i), flatten=TRUE)
#   message("Retrieving page ", i)
#   pages[[i+1]] <- mydata$`_embedded`$items
# }
# 
# #combine all into one
# filings <- rbind.pages(pages)
# 
# #check output
# nrow(filings)
# 
# # panos
# firstpage<- fromJSON("http://streetopedia.com/api/panos?_format=json&page=1")
# totalpage<- firstpage$pages
# 
# baseurl <- "http://streetopedia.com/api/panos?_format=json"
# pages <- list()
# for(i in 1:totalpage){
#   mydata <- fromJSON(paste0(baseurl, "&page=", i), flatten=TRUE)
#   # message("Retrieving page ", i)
#   pages[[i+1]] <- mydata$`_embedded`$items
# }
# 
# #combine all into one
# locations <- rbind.pages(pages)
# 
# #check output
# nrow(locations)
# 
# ##################
# # generate dates #
# ##################
# 
# locations$datetime<- as.POSIXct(locations$timestamp,format="%Y-%m-%d %H:%M:%S")
# locations$date<- as.Date(locations$datetime, format="%Y-%m-%d")
# locations$year<- year(locations$date)
# locations$month<- months(locations$date)
# 
# # count number of pics in each month
# pic_month<- count(locations,c("year","month"))
# print(pic_month)
# 
# #####################
# # from secure login #
# #####################
# 
# url<- "https://nyu-demo.survos.com/api1.0/security/login"
# result<- fromJSON(postForm(url, username="honggao",password="xxxx"))
# 
# accesstoken<- result$accessToken
# 
# 
# 
# 
# 
