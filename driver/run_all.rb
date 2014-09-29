require 'selenium-webdriver'
require 'mysql'
require 'yaml'

CONFIG = YAML::load_file('config.yml')

$chars = [('a'..'z'),('A'..'Z')].map{|i| i.to_a}.flatten
def rand_str(length)
    (0..length).map{ $chars[rand($chars.length)] }.join
end

$dbh = Mysql.new CONFIG['host'], CONFIG['user'], CONFIG['pass'], CONFIG['database']

def clear_log
    web_log = CONFIG['www_path'] + 'log_time.txt'
    `echo '' > #{web_log}`
end

def clear_rows
    sql = "DELETE FROM users"
    result = $dbh.query(sql);
end

def add_rows(num_rows=1)
    sql ="INSERT INTO users(username, fname, lname) VALUES "
    1.upto(num_rows) do
        sql += " ('#{rand_str(7)}', '#{rand_str(5)}', '#{rand_str(8)}'),"
    end
    sql = sql[0..-2]
    result = $dbh.query(sql)
    #result = `mysql -u #{CONFIG['user']} -p#{CONFIG['pass']} #{CONFIG['database']} -e "#{sql}"`
end

def double_rows
    sql = "SELECT COUNT(*) as num_rows FROM users"
    result = $dbh.query(sql);
    num_rows = result.fetch_row[0].to_i
    add_rows(num_rows)
end

def run_benchmarks
    driver = Selenium::WebDriver.for :chrome
    driver.manage.timeouts.page_load = 300 # 5 minutes

    add_rows
    driver.navigate.to 'http://localhost/web_performance/www/'

    18.times do
        double_rows
        driver.navigate.to 'http://localhost/web_performance/www/'
    end
    driver.quit
end

clear_log
clear_rows

run_benchmarks
