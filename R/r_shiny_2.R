# install.packages('shiny')
library(shiny)

ui<- fluidPage(
  #  Application title
  titlePanel("Prevalance Rates Example"),
  
  # Sidebar with sliders that demonstrate various available values

  sidebarLayout(
    sidebarPanel(
      # Simple interval
      sliderInput("PP", "Choose a prevalance (base) rate:", 
                   min=0, max=1, value=0.5, step=0.01),
      # add a submit button
      submitButton("Update")
    ),
    
    # Output tables summarizing the values entered & calculated
    mainPanel(
      tableOutput("values"),
      tableOutput("calculatevalues"),
      tableOutput("calculatevalues_2")
    )
  )
)


server<- function(input, output){
  
  # Reactive expression to compose a data frame containing all of
  # the count values
  countValues<- reactive({
    TP= 1000*input$PP*0.7
    TN= 1000*input$PP*0.3
    FP= 1000*(1-input$PP)*0.2
    FN= 1000*(1-input$PP)*0.8
    counttable<- data.frame(TP, TN, FP, FN)
  })
  
  tableValues <- reactive({
    # call counts from the last reactive function
    data<- countValues()
    
    # Compose two by two data table
    twoXtwo<-  function(x, y){
      tab = matrix(c(data$TP, data$TN, data$FP, data$FN), ncol=2)
      n = list(c("TRUE", "FALSE"), c("Positive", "Negative"))
      names(n) = c("x","y")
      dimnames(tab) = n
      tab = as.table(tab)
      dim
      tab
    }
    twoXtwo(x,y)
  }) 
  
  
  calculateValues<- reactive({
    # call counts from the count reactive function
    data<- countValues()
    
    # Compose another data frame of reactive values based on counts
    sens<- data$TP / (data$TP + data$FN)
    spec<- data$TN / (data$TN + data$FP)
    spec_1<- 1-spec
    LRpos<- sens / (1-spec)
    LRneg<- spec / (1-sens)
    PPP<- data$TP / (data$TP + data$FP)
    NPP<- data$TN / (data$TN + data$FN)
    j<- 0.1*input$PP
    PREpos<- j/(1-j)
    PREneg<- (1-j)/j
    POSTpos<- PREpos*LRpos
    POSTneg<- PREneg*LRneg
    PPP<- POSTpos/(POSTpos+1)
    NPP<- POSTneg/(POSTneg+1)
    IPPP<- PPP-j
    INPP<- NPP-j
    QPPP<- (PPP-j)/(1-j)
    QNPP<- (NPP-j)/(1-j)
    
    calculatetalbe<- data.frame(sens, spec, spec_1, LRpos, LRneg, PPP, NPP, PREpos, PREneg, POSTpos, POSTneg, 
                                IPPP, INPP, QPPP, QNPP, row.names="values")
  })
  
  
  # Show the values using an HTML table
  output$values <- renderTable({
    tableValues()
  })
  output$calculatevalues <- renderTable({
    data<- calculateValues()
    data[,1:7]
  })
  output$calculatevalues_2 <- renderTable({
    data<- calculateValues()
    data[,8:15]
  })
  
}


shinyApp(ui=ui, server=server)