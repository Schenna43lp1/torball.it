install:
	chmod +x setup.sh
	./setup.sh

start:
	docker compose up -d

stop:
	docker compose down

restart:
	docker compose down
	docker compose up -d --build

logs:
	docker compose logs -f

status:
	docker ps

update:
	git pull
	docker compose up -d --build
