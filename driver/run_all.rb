require 'selenium-webdriver'

driver = Selenium::WebDriver.for :chrome
driver.manage.timeouts.page_load = 300 # 5 minutes

10.times do
    3.times do
        driver.navigate.to 'http://localhost/perf_standards/www/'
    end

    driver.navigate.to 'http://localhost/perf_standards/www/add_rows.php'
end

driver.quit
