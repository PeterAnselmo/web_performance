require 'selenium-webdriver'
require 'mysql'
require 'yaml'

CONFIG = YAML::load_file('config.yml')

$chars = [('a'..'z'),('A'..'Z')].map{|i| i.to_a}.flatten
def rand_str(length)
    (0..length).map{ $chars[rand($chars.length)] }.join
end

$dbh = Mysql.new CONFIG['host'], CONFIG['user'], CONFIG['pass'], CONFIG['database']
def clear_rows
    sql = "DELETE FROM users"
    result = $dbh.query(sql);
end

def add_row
    sql ="INSERT INTO users(username, fname, lname) VALUES ('#{rand_str(7)}', '#{rand_str(5)}', '#{rand_str(8)}')"
    result = $dbh.query(sql)
end

def double_rows
    sql = "SELECT COUNT(*) as num_rows FROM users"
    result = $dbh.query(sql);
    num_rows = result.fetch_row[0].to_i
    1.upto(num_rows) do
        add_row
    end
end

def run_benchmarks
    driver = Selenium::WebDriver.for :chrome
    driver.manage.timeouts.page_load = 300 # 5 minutes
    100.times do
        1000.times do
            add_row
        end

        driver.navigate.to 'http://localhost/web_performance/www/'
    end
    driver.quit
end

clear_rows

run_benchmarks
