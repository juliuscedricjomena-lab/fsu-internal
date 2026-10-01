# Git Workflow Guide

## Branch Strategy

We follow a feature-branch workflow:

- **master**: Production-ready code, stable releases
- **develop**: Integration branch for features (if needed)
- **feature/\***: New features and improvements
- **bugfix/\***: Bug fixes
- **docs/\***: Documentation updates

## Commit Guidelines

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types
- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that don't affect code meaning (formatting, missing semicolons, etc)
- **refactor**: Code changes that neither fix bugs nor add features
- **perf**: Code changes that improve performance
- **test**: Adding missing tests or correcting existing tests
- **chore**: Changes to build process, dependencies, or tooling

### Example
```
feat(auth): add remember me functionality

Add remember-me checkbox to login form
Implement persistent session tokens
Update AuthController to handle remember functionality

Closes #123
```

## Workflow Steps

### Creating a Feature

1. Ensure you're on `master` and up to date:
   ```bash
   git checkout master
   git pull origin master
   ```

2. Create a feature branch:
   ```bash
   git checkout -b feature/feature-name
   ```

3. Make your changes and commit:
   ```bash
   git add <files>
   git commit -m "feat(scope): description"
   ```

4. Push to remote:
   ```bash
   git push -u origin feature/feature-name
   ```

### Before Committing

- Ensure code passes linting: `npm run lint`
- Run tests: `npm run test`
- Follow project conventions

## Common Commands

```bash
# View all branches
git branch -a

# View commit history
git log --oneline

# View changes
git diff

# Stash uncommitted changes
git stash
git stash pop

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1
```

## Important Notes

- Always create feature branches for new work
- Never commit directly to `master`
- Keep commits atomic and focused
- Use descriptive commit messages
- Rebase before merging to keep history clean
