#!/usr/bin/ruby

require 'selenium-webdriver'
require 'mysql'
require 'yaml'

CONFIG = YAML::load_file('../config.yml')
EXP_SIZE = 10
EXP_ROWS = 14
NUM_REPS = 1
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

def get_mem_usage
    result = `ps -eo rss,vsize,size,args | grep chrom | sort -rh`
    result.split("\n").first.split(" ").first
end

def log_mem_usage
    #update last row to store current memroy usage. Totally not thread safe
    mem_usage = get_mem_usage
    puts mem_usage
    sql = "update results set memory = #{mem_usage} order by id desc limit 1"
    $dbh.query(sql)
end

def clear_rows
    sql = "DELETE FROM users"
    result = $dbh.query(sql);
end

def add_rows(num_rows=1, field_size = 1)
    sql ="INSERT INTO users(username, fname, lname) VALUES "
    1.upto(num_rows) do
        sql += " ('#{rand_str(field_size)}', '#{rand_str(field_size)}', '#{rand_str(field_size)}'),"
    end
    sql = sql[0..-2]
    result = $dbh.query(sql)
    #result = `mysql -u #{CONFIG['user']} -p#{CONFIG['pass']} #{CONFIG['database']} -e "#{sql}"`
end

def double_rows(field_size = 1)
    sql = "SELECT COUNT(*) as num_rows FROM users"
    result = $dbh.query(sql);
    num_rows = result.fetch_row[0].to_i
    add_rows(num_rows, field_size)
end

def get_last_name
    sql = "SELECT username FROM users LIMIT 1"
    result = $dbh.query(sql);
    return result.fetch_row[0]
end

def run_benchmarks

    size = 1
    EXP_SIZE.times do
        driver = Selenium::WebDriver.for :chrome
        driver.manage.timeouts.page_load = 300 # 5 minutes
        clear_rows
        add_rows(1, size)
        MARKUP_TYPES.each do |type|
            NUM_REPS.times do
                driver.navigate.to "http://localhost/web_performance/www/index.php?type=#{type}"
                log_mem_usage
            end
        end

        EXP_ROWS.times do
            double_rows(size+1)
            NUM_REPS.times do
                MARKUP_TYPES.each do |type|
                    driver.navigate.to "http://localhost/web_performance/www/index.php?type=#{type}"
                    log_mem_usage
                end
            end
        end
        size *= 2
        driver.quit
    end
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
    sql = "SELECT type, num_rows, page_size,
                  MIN(request_start) as request_start,
                  MIN(response_end) as response_end,
                  MIN(render_time) as render_time,
                  MIN(memory) as memory
           FROM results
           GROUP BY type, num_rows, page_size"
    result = $dbh.query(sql)
    fh = File.open(RESULTS_FILE,'w')
    fh.puts ['Element',
             'Num Rows',
             'Page Size',
             'Response Time',
             'Render Time',
             'Memory'].join("\t")

    result.each_hash do |row|
        fh.puts [row['type'],
                 row['num_rows'].to_s,
                 row['page_size'].to_s,
                 row['response_end'].to_s,
                 row['render_time'].to_s,
                 row['memory'].to_s].join("\t")
    end
    fh.close
end


clear_log

run_benchmarks

dump_results
