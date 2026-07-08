
# Deloitte Challenge Log Analysis Report

## 1. Introduction

This document presents my analysis of the server log file to identify potential malicious activities and detect any cyberattacks. The goal of this analysis is to uncover suspicious behavior patterns and provide insights into possible attack stages observed in the log data.

## 2. Methodology

I used the following criteria to filter and identify suspicious log entries:

- **Unusual Status Codes**: Repeated 404 (Not Found), 403 (Forbidden), or 500 (Server Error) codes can suggest reconnaissance attempts or probing for vulnerable resources.
- **Sensitive Path Access Attempts**: Requests targeting paths like `/admin`, `/login`, and `/wp-admin` suggest attempts to access restricted resources.
- **Potential SQL Injection Attempts**: Indicators of SQL injection include characters such as single quotes (`'`), double hyphens (`--`), and comments (`#`).
- **Suspicious User Agents**: Known attack tools often leave specific user-agent strings, which can hint at automated attack attempts.

## 3. Results

### Reconnaissance and Probing Phase
I observed multiple 404 and 403 errors for sensitive files and directories, such as `/.bash_history` and `/.cvs`. These requests indicate an attempt to access hidden files that could expose sensitive information.

### Automated Tool Detection
Some requests had user agents resembling known web scraping and attack tools (e.g., bots and scanning software), suggesting automated reconnaissance efforts.

### SQL Injection Attempts
I identified entries containing characters indicative of SQL injection attempts, such as single quotes and encoded characters. This implies the attacker might be testing if certain URL parameters are vulnerable to SQL injection.

## 4. Query Examples

To analyze and extract suspicious activity, I used the following Linux commands after sftp-ing the log file to my VM:

1. **Frequent 404 Errors by IP**:
    ```bash
    grep ' 404 ' apache.log | awk '{print $1}' | sort | uniq -c | sort -nr | head -10
    ```

2. **Sensitive Path Access**:
    ```bash
    grep -E '/(\.bash_history|\.cvs|\.forward)' apache.log
    ```

3. **SQL Injection Patterns**:
    ```bash
    grep -E "'|%27|--|%23|#" apache.log
    ```

## 5. Conclusion

The analysis revealed signs of an ongoing reconnaissance and probing attempt, likely in the initial stages of a cyberattack. This probing activity included directory enumeration and SQL injection tests, suggesting the attacker was gathering information to exploit potential vulnerabilities.
