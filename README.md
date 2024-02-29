This Symfony project should run on a server that has PHP 8 running with composer installed and the symfony run-time.
To install the project use
#Composer install
#Symfony serve

The site should be accessible at http://localhost:8000

Upload the Excel file and view the grades in a matrix, as well as the calculated grade per student and the P'-value at the bottom.

Note there are some bugs at the bottom of the table; The P'-value in the last cell isn't calculated and the last student is overwritten with the P'-value row. This is a limitation of the way I used the excel reader class.
For the correlation function I did not have enough time, so I left it out.
