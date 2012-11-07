
//Define calendar(s): addCalendar ("Unique Calendar Name", "Window title", "Form element's name", Form name")
addCalendar("DatePublish", "Выберите дату", "DOC[DATETM]", "form1");
addCalendar("DateNews", "Выберите дату", "NEWS[DATETM]", "MainForm");
addCalendar("DateStartVote", "Выберите дату", "VOTE[DATE_START]", "MainForm");
addCalendar("DateStopVote", "Выберите дату", "VOTE[DATE_STOP]", "MainForm");
addCalendar("DateStart", "Выберите дату", "BLOCK[DATE_START]", "MainForm");
addCalendar("DateStop", "Выберите дату", "BLOCK[DATE_STOP]", "MainForm");
addCalendar("Date", "Выберите дату", "DATA[DATETM]", "MainForm");
addCalendar("DREGISTER", "Выберите дату", "DATA[DREGISTER]", "MainForm");
addCalendar("DSIGNING", "Выберите дату", "DATA[DSIGNING]", "MainForm");
addCalendar("DPROTOCOL", "Выберите дату", "DATA[DPROTOCOL]", "MainForm");
addCalendar("DPUBLICATION", "Выберите дату", "DATA[DPUBLICATION]", "MainForm");
addCalendar("DPROCEDURE", "Выберите дату", "DATA[DPROCEDURE]", "MainForm");
addCalendar("DOCUMENT_DATE", "Выберите дату", "DATA[DOCUMENT_DATE]", "MainForm");
// default settings for English
// Uncomment desired lines and modify its values
// setFont("verdana", 9);
setWidth(90, 1, 15, 1);
// setColor("#cccccc", "#cccccc", "#ffffff", "#ffffff", "#333333", "#cccccc", "#333333");
// setFontColor("#333333", "#333333", "#333333", "#ffffff", "#333333");
setFormat("dd.mm.yyyy");
// setSize(200, 200, -200, 16);

// setWeekDay(0);
// setMonthNames("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
// setDayNames("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
// setLinkNames("[Close]", "[Clear]");
