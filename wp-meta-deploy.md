# allWpMeta auto deploy

After changes take place inside the root of the project you can run `wp-meta-deploy.sh` from the root of the allWpMeta repository. This script commits and pushes all changes to the remote allWpMeta repository copies them over to the `filox` folder (same directory as `allWpMeta` folder) and commits and pushes to remote filox repository

## Warning

As of now, custom naming and/or folder nesting is not supported. For the script to work properly the 2 repository folders must be in the same directory.
E.g this will work

```
--FiloxRepos/
    --allWpMeta/
    --filox/
```

but these will not

```
--FiloxRepos/
    --allWpMeta/
    --customFiloxName/
```

or

```
--FiloxRepos/
    --parentFolder/allWpMeta
    --customFiloxName/
```
