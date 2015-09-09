# Install needed packages
install.packages('ggplot2')
library(ggplot2)



# set file directory
setwd("../Phychometrics")

# import csv file
mcsds<- read.csv("turk_job_mcsds_assignments.csv", stringsAsFactors = F)

# select target wave & completed assignments
mcsds<- subset(mcsds, WaveCode=="mcsds_live_99_100" | WaveCode=="mcsds_live_97_98",
               select=c(Id, MemberId, AssignmentStatusCode, Score, TaskId, q1:q33))
mcsds<- subset(mcsds, AssignmentStatusCode=="Submitted" | AssignmentStatusCode=="Approved")

18 items = "Attribution"
s1<- list("q1","q2","q4","q7","q8","q13","q16","q17","q18","q20","q21","q24","q25","q26","q27","q29","q31","q33")
15 items = "Denial"
s2<- list("q3","q5","q6","q9","q10","q11","q12","q14","q15","q19","q22","q23","q28","q30","q32")

recode values
for (i in c(s1,s2)){
  mcsds[[i]]<- ifelse(mcsds[[i]]==2, 0, mcsds[[i]])
}
for (i in c(s2)){
  mcsds[[i]]<- 1- mcsds[[i]]
}

# Aggregate scores
mcsds$Total<- 0
for (i in c(s1,s2)){
  mcsds$Total<- mcsds$Total+ mcsds[[i]]
}

mcsds$s1<- 0
for (i in c(s1)){
  mcsds$s1<- mcsds$s1+ mcsds[[i]]
}

mcsds$s2<- 0
for (i in c(s2)){
  mcsds$s2<- mcsds$s2+ mcsds[[i]]
}

# dx cutpoints
for (i in 1:33){
  mcsds[[paste0("SD",i,sep="")]]<- ifelse(mcsds$Total>=i, 1, 0)
  mcsds[[paste0("SD_s1",i,sep="")]]<- ifelse(mcsds$s1>=i, 1, 0)
  mcsds[[paste0("SD_s2",i,sep="")]]<- ifelse(mcsds$s2>=i, 1, 0)
}

# counts across cut-points
for (i in 1:33){
  Total score
  mcsds[[paste0("TP_t",i,sep="")]]<- ifelse(mcsds$Total>=i, mcsds$Total, NA)
  mcsds[[paste0("FN_t",i,sep="")]]<- ifelse(mcsds$Total>=i, 33-mcsds$Total, NA)
  mcsds[[paste0("FP_t",i,sep="")]]<- ifelse(mcsds$Total<i, mcsds$Total, NA)
  mcsds[[paste0("TN_t",i,sep="")]]<- ifelse(mcsds$Total<i, 33-mcsds$Total, NA)
  scale 1
  mcsds[[paste0("TP_s1",i,sep="")]]<- ifelse(mcsds$s1>=i, mcsds$s1, NA)
  mcsds[[paste0("FN_s1",i,sep="")]]<- ifelse(mcsds$s1>=i, 18-mcsds$s1, NA)
  mcsds[[paste0("FP_s1",i,sep="")]]<- ifelse(mcsds$s1<i, mcsds$s1, NA)
  mcsds[[paste0("TN_s1",i,sep="")]]<- ifelse(mcsds$s1<i, 18-mcsds$s1, NA)
  scale 2
  mcsds[[paste0("TP_s2",i,sep="")]]<- ifelse(mcsds$s2>=i, mcsds$s2, NA)
  mcsds[[paste0("FN_s2",i,sep="")]]<- ifelse(mcsds$s2>=i, 15-mcsds$s2, NA)
  mcsds[[paste0("FP_s2",i,sep="")]]<- ifelse(mcsds$s2<i, mcsds$s2, NA)
  mcsds[[paste0("TN_s2",i,sep="")]]<- ifelse(mcsds$s2<i, 15-mcsds$s2, NA)
}

varlist<- c("TP_t","FN_t","FP_t","TN_t","TP_s1","FN_s1","FP_s1","TN_s1","TP_s2","FN_s2","FP_s2","TN_s2")
cutpoint<- 1:33
mcsds_cutpoint<- data.frame(cutpoint)
for (i in varlist){
  i<- colSums(mcsds[grep(i, names(mcsds), value=TRUE)], na.rm=T, dims=1)
  mcsds_cutpoint<- data.frame(mcsds_cutpoint, i)
}
colnames(mcsds_cutpoint)<- c("cutpoint", varlist)

Sensitivity and Specificity
for (i in c("t","s1","s2")){
  mcsds_cutpoint[[paste0("sens_",i,sep="")]]<-
    mcsds_cutpoint[[paste0("TP_",i,sep="")]]/
    (mcsds_cutpoint[[paste0("TP_",i,sep="")]]+mcsds_cutpoint[[paste0("FN_",i,sep="")]])
  mcsds_cutpoint[[paste0("spec_",i,sep="")]]<-
    mcsds_cutpoint[[paste0("TN_",i,sep="")]]/
    (mcsds_cutpoint[[paste0("TN_",i,sep="")]]+mcsds_cutpoint[[paste0("FP_",i,sep="")]])
  mcsds_cutpoint[[paste0("spec_",i,"_1",sep="")]]<- 1-mcsds_cutpoint[[paste0("spec_",i,sep="")]]
}

identify successive cutpoint improvements
for (i in c("t","s1","s2")){
  mcsds_cutpoint[[paste0("cuts_",i,sep="")]]<-
    ifelse(mcsds_cutpoint[[paste0("sens_",i,sep="")]]!=mcsds_cutpoint[[paste0("sens_",i,sep="")]][-1],
           mcsds_cutpoint$cutpoint, NA)
  mcsds_cutpoint[[paste0("cut",i,sep="")]]<- max(mcsds_cutpoint[[paste0("cuts_",i,sep="")]], na.rm=T)
}

ROC
p1<- ggplot(mcsds_cutpoint, aes(spec_t_1, sens_t))
p1+geom_point(colour="dodgerblue3", size=3)+
  theme(panel.background=element_blank())+
  theme(panel.background= element_rect(color="black"))+
  coord_cartesian(ylim=c(0,1.1),xlim=c(-0.1,1))

p2<- ggplot(mcsds_cutpoint, aes(spec_s1_1, sens_s1))
p2+geom_point(colour="springgreen4", size=3)+
  theme(panel.background=element_blank())+
  theme(panel.background= element_rect(color="black"))+
  coord_cartesian(ylim=c(0,1.1),xlim=c(-0.1,1))

p3<- ggplot(mcsds_cutpoint, aes(spec_s2_1, sens_s2))
p3+geom_point(colour="red3", size=3)+
  theme(panel.background=element_blank())+
  theme(panel.background= element_rect(color="black"))+
  coord_cartesian(ylim=c(0,1.1),xlim=c(-0.1,1))

p4<- ggplot(mcsds_cutpoint, aes(spec_t_1, sens_t))
p4+stat_summary(aes(y=sens_t),fun.y=mean, geom="line", size=1.4)+
  theme(panel.background=element_blank())+
  theme(panel.background= element_rect(color="black"))+
  coord_cartesian(ylim=c(0,1.1),xlim=c(-0.1,1))












