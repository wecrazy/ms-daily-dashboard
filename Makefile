.DEFAULT_GOAL := help
.PHONY: help install dev serve test lint clean env db-setup db-setup-force \
        export-tasks export-tasks-now export-stages export-sla \
        apk-debug apk-release apk-clean apk-install

# ── Help ────────────────────────────────────────────

help: ## Show this help
	@echo ""
	@echo "  MS Daily Dashboard — Make Commands"
	@echo "  ───────────────────────────────────"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'
	@echo ""

# ── PHP ─────────────────────────────────────────────

install: ## Install production dependencies
	composer install --no-dev --optimize-autoloader

dev: ## Install all dependencies (including dev)
	composer install

serve: ## Start PHP dev server on port 8090 (LAN accessible)
	php -S 0.0.0.0:8090 -t public

test: ## Run PHPUnit tests
	./vendor/bin/phpunit

lint: ## Check PHP syntax across src/
	find src/ -name "*.php" -exec php -l {} \;

clean: ## Remove vendor/ and composer.lock
	rm -rf vendor/ composer.lock

env: ## Create .env from .env.example
	@if [ ! -f .env ]; then cp .env.example .env && echo ".env created"; else echo ".env already exists"; fi

db-setup: ## Create database, tables, and seed users
	php bin/db-setup.php

db-setup-force: ## Same as db-setup, skip confirmation
	php bin/db-setup.php --force

# ── Cron Exports ────────────────────────────────────

export-tasks: ## Run scheduled task export
	php bin/export-tasks.php

export-tasks-now: ## Run real-time task export
	php bin/export-tasks-now.php

export-stages: ## Run stage data export
	php bin/export-stages.php

export-sla: ## Run SLA deadline export
	php bin/export-sla.php

# ── Android ─────────────────────────────────────────

APK_DEBUG   = android/app/build/outputs/apk/debug/app-debug.apk
APK_RELEASE = android/app/build/outputs/apk/release/app-release.apk

apk-debug: ## Build debug APK
	cd android && ./gradlew assembleDebug --no-daemon
	@echo "\n✅  Debug APK → $(APK_DEBUG)"
	@ls -lh $(APK_DEBUG)

apk-release: ## Build release APK (requires signing config)
	cd android && ./gradlew assembleRelease --no-daemon
	@echo "\n✅  Release APK → $(APK_RELEASE)"
	@ls -lh $(APK_RELEASE)

apk-clean: ## Clean Android build artifacts
	cd android && ./gradlew clean --no-daemon
	@echo "✅  Android build cleaned"

apk-install: apk-debug ## Build & install debug APK on device
	cd android && ./gradlew installDebug --no-daemon
	@echo "✅  APK installed on device"
