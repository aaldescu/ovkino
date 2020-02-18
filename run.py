#import libs
import sys
from urllib.request import urlopen
from bs4 import BeautifulSoup
import sqlite3
import re
import time
import datetime

print("Start search....")

#connect to DB
db=sqlite3.connect('ovkino.db')

#specify the url
kinofans_page = 'https://www.kinofans.com/kinoprogramm/Frankfurt+am+Main' 

html = urlopen(kinofans_page).read()

soup = BeautifulSoup(html, 'html.parser')

movies = soup.select('a[title*="(OV)"]')


for link in movies:
    link_text = link.get_text();
    print ("\nMovie: " + link.get_text())
    time.sleep(1)
    movie_page_url = "https://www.kinofans.com" + link.get('href')
    print ("\nURL: " + movie_page_url)
    playtime_html = urlopen(movie_page_url).read()
    timepage_soup = BeautifulSoup(playtime_html,'html.parser')
    kinos = timepage_soup.select('.KinoProgram > h2')
    
    for kino in kinos:
        time_table = kino.findNext("table")
        i = 0
        days_dict = {}
        for row in time_table.findAll("tr"):
            i = i + 1
            j = 0
            for col in row.findAll("td"):
                j = j + 1
                col_txt = col.get_text()
                found_hour_pattern = re.search('([0-9][0-9]:[0-9][0-9])', col_txt )
                found_day_pattern = re.search('(Mo,)|(Di,)|(Mi,)|(Do,)|(Fr,)|(Sa,)|(So,)', col_txt )
                if (found_hour_pattern or found_day_pattern ):
                    if col.div:
                        for div in col.findAll("div"):
                            try:
                                cursor = db.cursor()
                                movie_name = link.get_text()
                                kino_name = kino.get_text()
                                hour = div.get_text()
                                day = days_dict[j]
                                sql_query = "INSERT INTO playtime (kino, movie, hour , day) VALUES(?,?,?,?)"
                                count = cursor.execute(sql_query,(kino_name,movie_name,hour,day))
                                db.commit()
                                cursor.close()
                            except sqlite3.Error as error:
                                print("Failed to insert: ", error, sql_query)
                                sys.exit(0)
                            
                            print(link.get_text() +" : " + kino.get_text() +" : "+ div.get_text() + " " + str(days_dict[j]))
                                    
                    else:
                        day_string = str(col.get_text().strip("Heute"))
                        match = re.findall(r"\d+", day_string )
                        today = datetime.datetime.today()
                        if match:
                                YYYY = str(today.year)
                                MM = '{:02d}'.format(today.month)
                                DD = '{:02d}'.format(int(match[0]))
                                days_dict[j] =   YYYY + "-" + MM + "-" + DD
                        #print(col.get_text().strip("Heute") + " ["+ str(i)+"]["+str(j)+"]")
        #print(days_dict)
                                            
    print ("\nKinos: " + str(len(kinos))) 
    
    print ("\n\n")
        
print("End search....")
