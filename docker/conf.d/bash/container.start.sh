# Zapnutí SSH agenta
# eval `ssh-agent -s`

(cd /var/www/html/tools/ && ./start.sh)

# Spuštění Apache
echo "Spouštím Apache do pozadí..."
apache2ctl -D FOREGROUND
