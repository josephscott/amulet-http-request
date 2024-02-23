# Changelog

## ????
- Tests now use the Pest "--parallel" option
- Rework curl timing details, provide direct curl numbers
- Fully qualify PHP functions
- Bump pest to 2.24
- Bump php-cs-fixer to 3.49

## 0.0.2 : 2023-12-18
- Default headers: connection, accept, user-agent
- Can provide options on each verb method
- Support HTTP requests without curl, using file_get_contents()
- Use content-type application/x-www-form-urlencoded when sending body content

## 0.0.1 : 2023-12-02
- Basic working version
