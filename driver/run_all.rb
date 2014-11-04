require 'selenium-webdriver'
require 'mysql'
require 'yaml'

CONFIG = YAML::load_file('config.yml')
EXP_ROWS = 17
NUM_REPS = 3
RESULTS_FILE = 'results.txt'
MARKUP_TYPES = ['table','fixed-table','list','checkbox','dropdown','autocomplete']

$chars = [('a'..'z'),('A'..'Z')].map{|i| i.to_a}.flatten
def rand_str(length)
    (0..length).map{ $chars[rand($chars.length)] }.join
end

$dbh = Mysql.new CONFIG['host'], CONFIG['user'], CONFIG['pass'], CONFIG['database']

def clear_log
    web_log = CONFIG['www_path'] + 'log_time.txt'
    `echo '' > #{web_log}`
    `echo '' > #{RESULTS_FILE}`
    sql = "DELETE FROM results"
    result = $dbh.query(sql)
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

def get_last_name
    sql = "SELECT username FROM users LIMIT 1"
    result = $dbh.query(sql);
    return result.fetch_row[0]
end

def run_benchmarks
    driver = Selenium::WebDriver.for :chrome
    driver.manage.timeouts.page_load = 300 # 5 minutes

    add_rows
    MARKUP_TYPES.each do |type|
        NUM_REPS.times do
            driver.navigate.to "http://localhost/web_performance/www/index.php?type=#{type}"
        end
    end

    EXP_ROWS.times do
        double_rows
        NUM_REPS.times do
            MARKUP_TYPES.each do |type|
                driver.navigate.to "http://localhost/web_performance/www/index.php?type=#{type}"
            end
        end
    end
    driver.quit
end

def run_autocomplete_benchmarks
    driver = Selenium::WebDriver.for :chrome
    wait = Selenium::WebDriver::Wait.new(:timeout => 10)
    driver.manage.timeouts.page_load = 300 # 5 minutes

    add_rows
    EXP_ROWS.times do
        double_rows
        MARKUP_TYPES.each do |type|
            driver.navigate.to "http://localhost/web_performance/www/index.php?type=autocomplete"

            driver.navigate.to "http://localhost/web_performance/www/index.php?type=autocomplete-ajax"
            textbox = nil
            wait.until{ textbox = driver.find_element(:class, 'ui-autocomplete-input') }
            start_time = Time.now
            textbox.send_keys get_last_name
            option = nil
            wait.until{ option = driver.find_element(:class, 'ui-menu-item') }
            end_time = Time.now
            diff = end_time - start_time
            puts diff * 1000
            textbox.send_keys(:arrow_down)
            textbox.send_keys(:enter)
        end
    end
    driver.quit
end

def dump_results
    sql = "SELECT type, num_rows, MIN(page_size) as page_size,
                                  MIN(request_start) as request_start,
                                  MIN(response_end) as response_end,
                                  MIN(render_time) as render_time
           FROM results
           GROUP BY type, num_rows"
    result = $dbh.query(sql)
    fh = File.open(RESULTS_FILE,'w')
    fh.puts ['Element',
             'Num Rows',
             'Page Size',
             'Response Time',
             'Render Time'].join("\t")

    result.each_hash do |row|
        fh.puts [row['type'],
                 row['num_rows'].to_s,
                 row['page_size'].to_s,
                 row['response_end'].to_s,
                 row['render_time'].to_s].join("\t")
    end
    fh.close
end


clear_log
clear_rows

run_benchmarks

dump_results
