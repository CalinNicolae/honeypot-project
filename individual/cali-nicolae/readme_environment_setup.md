# Environment Setup Guide

## Part 1: NGINX Setup

### Step 1: Install NGINX and PHP

Update package sources and install necessary dependencies:

```bash
sudo apt install curl gnupg2 ca-certificates lsb-release debian-archive-keyring git
```

Add the NGINX repository and import the signing key:

```bash
curl https://nginx.org/keys/nginx_signing.key | gpg --dearmor | sudo tee /usr/share/keyrings/nginx-archive-keyring.gpg >/dev/null
gpg --dry-run --quiet --no-keyring --import --import-options import-show /usr/share/keyrings/nginx-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/nginx-archive-keyring.gpg] http://nginx.org/packages/debian `lsb_release -cs` nginx" | sudo tee /etc/apt/sources.list.d/nginx.list
sudo apt update
sudo apt install nginx
PATH=/usr/sbin/:$PATH
nginx -v
```

Install PHP and configure permissions:

```bash
sudo apt install php8.2-fpm -y
sudo usermod -aG www-data nginx
```

### Step 2: Configure NGINX

Open the NGINX configuration file and set up a server block:

```bash
sudo nano /etc/nginx/conf.d/default.conf
```

Add the following server block configuration:

```nginx
server {
    listen 80;
    server_name servername;

    root /path/to/server/files;
    index index.html index.htm index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Update the hosts file:

Windows: `C:\Windows\System32\Drivers\etc\hosts`

I added the following line:

```plaintext
192.168.197.135 individual.honeypot
```

### Step 3: HTTPS (SSL/TLS) Configuration

Generate self-signed certificates:

```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/nginx-selfsigned.key -out /etc/ssl/certs/nginx-selfsigned.crt
```

Update NGINX configuration to enable HTTPS:

```bash
sudo nano /etc/nginx/conf.d/default.conf
```

Add the HTTPS server block:

```nginx
server {
    listen 443 ssl;
    server_name servername;

    ssl_certificate /etc/ssl/certs/nginx-selfsigned.crt;
    ssl_certificate_key /etc/ssl/private/nginx-selfsigned.key;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /path/to/server/files;
    index index.html index.htm index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Redirect HTTP to HTTPS:

```nginx
server {
    listen 80;
    server_name servername;

    return 301 https://$host$request_uri;
}
```

### Step 4: Security Headers

Edit `/etc/nginx/conf.d/default.conf` to include security headers in the HTTPS server block:

```nginx
add_header X-Frame-Options "DENY";
add_header X-Content-Type-Options "nosniff";
add_header Strict-Transport-Security "max-age=63072000; includeSubdomains";
```

### Step 5: Restart NGINX

```bash
sudo systemctl restart nginx
```

## Part 2: Elastic Stack Setup

### Step 1: Install Elasticsearch

Update package sources and add the Elastic Stack signing key:

```bash
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
```

Add the Elastic Stack repository:

```bash
echo "deb https://artifacts.elastic.co/packages/8.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-8.x.list
sudo apt update
```

Install Elasticsearch:

```bash
sudo apt install elasticsearch
```

Enable and start Elasticsearch:

```bash
sudo systemctl enable elasticsearch
sudo systemctl start elasticsearch
```

### Step 2: Configure Elasticsearch

Open the configuration file:

```bash
sudo nano /etc/elasticsearch/elasticsearch.yml
```

Add basic configurations for network access:

```yaml
network.host: 0.0.0.0
```

Save and close the file, then restart Elasticsearch:

```bash
sudo systemctl restart elasticsearch
```

Test Elasticsearch by accessing `http://<server_ip>:9200` and verifying the JSON response.

### Step 3: Install Kibana

Install Kibana:

```bash
sudo apt install kibana
```

Enable and start Kibana:

```bash
sudo systemctl enable kibana
sudo systemctl start kibana
```

### Step 4: Configure Kibana

Open the Kibana configuration file:

```bash
sudo nano /etc/kibana/kibana.yml
```

Set up basic configurations:

```yaml
server.host: "0.0.0.0"
elasticsearch.hosts: ["http://localhost:9200"]
```

Save and restart Kibana:

```bash
sudo systemctl restart kibana
```

Access Kibana by navigating to `http://<server_ip>:5601` in your browser.

## Part 3: Filebeat Setup

### Step 1: Install Filebeat

Update the package list and install Filebeat:

```bash
sudo apt update
sudo apt install filebeat
```

Enable and start Filebeat:

```bash
sudo systemctl enable filebeat
sudo systemctl start filebeat
```

### Step 2: Configure Filebeat for NGINX Logs

Open the Filebeat configuration file:

```bash
sudo nano /etc/filebeat/filebeat.yml
```

Enable the NGINX module:

```bash
sudo filebeat modules enable nginx
```

Configure Filebeat to send data to Elasticsearch. In the output section, set the Elasticsearch host:

```yaml
output.elasticsearch:
  hosts: ["192.168.197.136:9200"]
```

Test the configuration:

```bash
sudo filebeat test config
```

### Step 3: Enable and Configure NGINX Module in Filebeat

Configure the NGINX module to specify the path of your NGINX log files (if different from the default):

```bash
sudo nano /etc/filebeat/modules.d/nginx.yml
```

Update the access and error log paths if necessary:

```yaml
- module: nginx
  access:
    enabled: true
    var.paths: ["/var/log/nginx/access.log*"]
  error:
    enabled: true
    var.paths: ["/var/log/nginx/error.log*"]
```

### Step 4: Start Filebeat and Verify Data in Kibana

Start Filebeat to begin sending logs:

```bash
sudo systemctl restart filebeat
```

Go to Kibana and verify that logs are being received:

In Kibana, go to Discover and select the `filebeat-*` index pattern to view incoming logs.

## Part 4: WAF Setup

### Step 1: Install Required Packages

Update package list and install dependencies:

```bash
sudo apt-get update
sudo apt-get install git g++ apt-utils autoconf automake build-essential libcurl4-openssl-dev libgeoip-dev liblmdb-dev libpcre2-dev libtool libxml2-dev libyajl-dev pkgconf zlib1g-dev
```

### Step 2: Get and Install ModSecurity

Clone the ModSecurity repository:

```bash
git clone --recursive https://github.com/SpiderLabs/ModSecurity
cd ModSecurity/
```

Initialize submodules, build, and install:

```bash
git submodule init
git submodule update
./build.sh
./configure --with-pcre2
make
sudo make install
```

### Step 3: Get the NGINX Plugin

Create a directory for the plugin and download it:

```bash
sudo mkdir /usr/local/src/cpg
cd /usr/local/src/cpg
sudo git clone https://github.com/SpiderLabs/ModSecurity-nginx
```

Download and extract NGINX source:

```bash
sudo wget http://nginx.org/download/nginx-1.26.2.tar.gz
sudo tar -xvzf nginx-1.26.2.tar.gz
cd nginx-1.26.2/
```

Build the ModSecurity module for NGINX:

```bash
sudo ./configure --with-compat --with-openssl=/usr/include/openssl/ --add-dynamic-module=/usr/local/src/cpg/ModSecurity-nginx
sudo make modules
sudo cp objs/ngx_http_modsecurity_module.so /etc/nginx/modules/
```

### Step 4: Configure NGINX to Use ModSecurity

Open the main NGINX configuration file:

```bash
sudo nano /etc/nginx/nginx.conf
```

Add the following line at the top to load the ModSecurity module:

```nginx
load_module modules/ngx_http_modsecurity_module.so;
```

### Step 5: Get the OWASP Core Rule Set (CRS)

Clone the Core Rule Set repository:

```bash
sudo git clone https://github.com/coreruleset/coreruleset /opt/coreruleset
```

Move and rename example configuration files:

```bash
sudo mv /opt/coreruleset/crs-setup.conf.example /opt/coreruleset/crs-setup.conf
sudo mv /opt/coreruleset/rules/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf.example /opt/coreruleset/rules/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf
```

Copy ModSecurity configuration files:

```bash
sudo cp ~/ModSecurity/unicode.mapping /etc/nginx/modsec
sudo cp ~/ModSecurity/modsecurity.conf-recommended /etc/nginx/modsec/modsecurity.conf
```

Edit the modsecurity.conf file:

```bash
sudo nano /etc/nginx/modsec/modsecurity.conf
```

Change `SecRuleEngine DetectionOnly` to `SecRuleEngine On`.

Create a main configuration file for ModSecurity:

```bash
sudo nano /etc/nginx/modsec/main.conf
```

Add the following lines:

```nginx
Include /etc/nginx/modsec/modsecurity.conf
Include /opt/coreruleset/crs-setup.conf
Include /opt/coreruleset/rules/*.conf
```

### Step 6: Enable ModSecurity in the Virtual Host

Edit the NGINX virtual host configuration to enable ModSecurity:

```nginx
server {
    modsecurity on;
    modsecurity_rules_file /etc/nginx/modsec/main.conf;
}
```

Save and close the configuration file.

### Step 7: Test and Verify

Restart NGINX to apply the WAF configuration:

```bash
sudo systemctl restart nginx
```

Test your site by browsing to it and attempting various payloads to ensure the WAF is functioning.

### Step 8: Forward WAF Logs to Elastic Stack

To monitor and analyze ModSecurity WAF logs in the Elastic Stack, we need to configure Filebeat to capture these logs and send them to Elasticsearch for visualization in Kibana.

Enable Filebeat Module for ModSecurity:

Filebeat includes modules for various logs. I used the modsecurity module, which is specifically designed for ModSecurity log formats.

Enable the ModSecurity module in Filebeat:

```bash
sudo filebeat modules enable modsecurity
```

### Configure the ModSecurity Module in Filebeat

Open the configuration file for the ModSecurity module:

```bash
sudo nano /etc/filebeat/modules.d/modsecurity.yml
```

Set the path to your ModSecurity logs. Replace `<modsecurity_log_path>` with the actual path of your ModSecurity log files (typically in `/var/log/nginx/` if ModSecurity logs are configured there):

```yaml
- module: modsecurity
  log:
    enabled: true
    var.paths: ["<modsecurity_log_path>"]
```

Save and close the file.

### Configure Filebeat to Output to Elasticsearch

Open the main Filebeat configuration file:

```bash
sudo nano /etc/filebeat/filebeat.yml
```

Configure the output to send logs directly to Elasticsearch:

```yaml
output.elasticsearch:
  hosts: ["192.168.197.136:9200"]
```

Optional: If your Elasticsearch instance requires authentication, add the username and password fields in the output configuration:

```yaml
output.elasticsearch:
  hosts: ["<elasticsearch_ip>:<elasticsearch_port>"]
  username: "elastic"
  password: "your_password"
```

### Test Filebeat Configuration

Before starting Filebeat, test the configuration for any syntax issues:

```bash
sudo filebeat test config
```

If there are no errors, proceed to start or restart Filebeat:

```bash
sudo systemctl restart filebeat
```

### Verify ModSecurity Logs in Kibana

Log into Kibana and navigate to Discover.

Look for the `filebeat-*` index pattern. If the pattern is not present:

1. Go to **Management > Index Patterns** and create a new pattern with `filebeat-*` as the pattern.
2. Once in Discover, you should see logs from ModSecurity, with fields specific to ModSecurity events, such as:
    - `event.category` for the type of security event
    - `http.request.method`, `http.response.status_code` for HTTP request/response details
    - `rule.id` for the specific ModSecurity rule triggered

Check the logs in Kibana under Discover to view incoming WAF alerts and activities.

## Part 5: Kibana Dashboards

Log into Kibana.

1. Create a new dashboard.
2. Create a new visualization.

I created 2 visualizations in my dashboard:

- One based on the count of requests and type of requests sorted by IP address.
- Another based on the percentage of status response codes as a pie chart.
- Link to Youtube explanation video : https://youtu.be/F52SavW9vms