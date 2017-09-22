//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "AppDelegate.h"
#import "AlbumSync.h"
#import "SetupView.h"
#import "TransferView.h"
#import "MyMusicPlayer.h"

#define UPLOAD_ITEM_MAX     512 //for debug, need to set it to 1000

AlbumSync *defaultAlbumSync;

@interface AlbumSync ()

@property BOOL videoUploadEnabled;

/* save data */
@property NSMutableArray *assetTodoArray;
@property NSMutableArray *UploadAlbumsArray;


/* working */
@property dispatch_queue_t uploadWorkQueue;

/* upload */
@property BOOL uploadErrorPending;
@property NSUInteger lastUploadTime;
@property NSUInteger retryCount;
@property NSUInteger transfer_id;
@property NSInteger serverTimeDelta;

/* status */
@property NSString *status;
@property float current_progress;
@property NSString *current_file_name;
@property NSString *current_file_size;
@property PHAsset *current_asset;

@end

@implementation AlbumSync

static dispatch_queue_t actionWorkQueue;

void AlbumSync_init(void)
{
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        actionWorkQueue = dispatch_queue_create("actionWorkQueue", DISPATCH_QUEUE_SERIAL);
        
        
//For debug
//        setup_set_bool(SETUP_KEY_UPLOAD_EN, NO);
        
    });

//    AppDelegate *appDelegate = (AppDelegate *)[[UIApplication sharedApplication] delegate];
    if (!defaultAlbumSync) {
        defaultAlbumSync = [[AlbumSync alloc] init];
        
//        [MyMusicPlayer initSession];
//        defaultAlbumSync.musicPlayer = [[MyMusicPlayer alloc] init];
    }
//    NSURL *musicUrl = [[NSBundle mainBundle] URLForResource:@"notify_uploading" withExtension:@"mp3"];
//    [defaultAlbumSync.musicPlayer playSongWithUrl:musicUrl songTitle:@"uploading" artist:@""];

    AlbumSync_reset();
}

void AlbumSync_exeOnActionWorkQueue(void (^exeHandler)(void))
{
    dispatch_async(actionWorkQueue, ^{
        exeHandler();
    });
}

void AlbumSync_reset(void)
{
    AlbumSync_Stop();

    defaultAlbumSync.retryCount = 0;
    defaultAlbumSync.uploadOnGoing = NO;
    defaultAlbumSync.uploadComplete = NO;
    defaultAlbumSync.lastUploadTime = 0;

#if TARGET_OS_SIMULATOR
    setup_set(SETUP_KEY_SERVER_URL, @"http://localhost:10080/index.php");
    setup_set(SETUP_KEY_USER_ID, @"1");
    setup_set(SETUP_KEY_DEV_ID, @"1");
    setup_set(SETUP_KEY_USER_TOKEN, @"b6d13129f8e94693287d1d0d6c3bd8f7");
    
    
#endif

    defaultAlbumSync.uploadServerURLString = setup_get_string(SETUP_KEY_SERVER_URL);

    if (!setup_get_bool(SETUP_KEY_UPLOAD_EN)) {
        defaultAlbumSync.stopPending = YES;
        defaultAlbumSync.status = @"stop";
    }else {
        defaultAlbumSync.stopPending = NO;
        defaultAlbumSync.status = @"connecting";
    }

    AlbumSync_Start();
}

void AlbumSync_Start(void)
{
/*
    if (defaultAlbumSync.photoLibChangePending) {
        defaultAlbumSync.photoLibChangePending = NO;
        MyInf(@"photo change pending, start");
        goto start_upload;
    }
    
    if (defaultAlbumSync.reachabilityChangePending && !defaultAlbumSync.uploadComplete) {
        defaultAlbumSync.reachabilityChangePending = NO;
        MyInf(@"reachability change and not complete, start");
        goto start_upload;
    }

    if (defaultAlbumSync.lastUploadTime > [[NSDate date] timeIntervalSince1970] - 300) {
        MyInf(@"ignore, time < 300");
        return;
    }
 */
    if (defaultAlbumSync.uploadOnGoing) {
        if ([defaultAlbumSync.status compare:@"uploading_progress"] == NSOrderedSame)
            [defaultAlbumSync uploadInfoNotify:@"uploading_file"];
        [defaultAlbumSync uploadInfoNotify:defaultAlbumSync.status];
        return;
    }
    
start_upload:
    [defaultAlbumSync startUploadBgTask];
    defaultAlbumSync.uploadComplete = NO;
    defaultAlbumSync.uploadOnGoing = YES;
    defaultAlbumSync.uploadErrorPending = NO;

    dispatch_async(actionWorkQueue, ^{
        @try {
            dispatch_async(actionWorkQueue, ^{
                
                //TODO: add retry for error
                
                //            [defaultAlbumSync continueUpload];
                
                while (!defaultAlbumSync.stopPending && !defaultAlbumSync.uploadComplete) {
                    [defaultAlbumSync sendData:nil params:@{
                                                                                    @"action" : @"token",
                                                                                    @"sub_action" : @"query",
                                                                                    } url:nil progressHandler:nil verify:YES error:nil];
                    if (!defaultAlbumSync.uploadErrorPending && [defaultAlbumSync getAlbumsTodoList] > 0) {
                        MyInf(@"continue upload");
                        [defaultAlbumSync uploadInfoNotify:@"uploading"];
                        [defaultAlbumSync continueUpload];
                    }
                    if (!defaultAlbumSync.uploadErrorPending && [defaultAlbumSync getAlbumsTodoList] == 0) {
                        MyInf(@"upload albums");
                        [defaultAlbumSync uploadInfoNotify:@"uploading_albums"];
                        [defaultAlbumSync doAlbumsListUpload];
                    }
                    if (!defaultAlbumSync.uploadErrorPending && [defaultAlbumSync getAlbumsTodoList] == 0) {
                        defaultAlbumSync.uploadComplete = YES;
                        [defaultAlbumSync uploadInfoNotify:@"complete"];
                        MyInf(@"upload complete");
                        break;
                    }
                    if (defaultAlbumSync.uploadErrorPending)
                        break;
                }
                defaultAlbumSync.uploadOnGoing = NO;
                if (defaultAlbumSync.stopPending) {
                    [defaultAlbumSync uploadInfoNotify:@"stop"];
                }else {
                    defaultAlbumSync.lastUploadTime = [[NSDate date] timeIntervalSince1970];
                    if (defaultAlbumSync.uploadErrorPending) {
                        [defaultAlbumSync uploadInfoNotify:@"error"];
                    }
                }
            });
        } @catch (NSException *exception) {
            MyErr(@"catch exception\n");
            MyErr(@"Name: %@", exception.name);
            MyErr(@"Reason: %@", exception.reason );
            MyErr(@"CallStack: %@", [exception callStackSymbols]);
            
            defaultAlbumSync.status = @"connecting";

        } @finally {
            ;
        }
    });
}


/*

void AlbumSync_Start(NSData *serverResponseData)
{
    return; //todo:
    
    dispatch_async(actionWorkQueue, ^{
        @try {
            runSyncTask(serverResponseData, true);
        } @catch (NSException *exception) {
            MyErr(@"catch exception\n");
            MyErr(@"Name: %@", exception.name);
            MyErr(@"Reason: %@", exception.reason );
            MyErr(@"CallStack: %@", [exception callStackSymbols]);
            
            defaultAlbumSync.status = @"connecting";
            
        } @finally {
            ;
        }
    });
}


void AlbumSync_Continue(NSData *serverResponseData)
{
    dispatch_async(actionWorkQueue, ^{
        @try {
            runSyncTask(serverResponseData, false);
        } @catch (NSException *exception) {
            MyErr(@"catch exception\n");
            MyErr(@"Name: %@", exception.name);
            MyErr(@"Reason: %@", exception.reason );
            MyErr(@"CallStack: %@", [exception callStackSymbols]);
            
            defaultAlbumSync.status = @"connecting";
            
        } @finally {
            ;
        }
    });
}
 */


void AlbumSync_Stop(void)
{
    MyInf(@"cancel all upload tasks");
    defaultAlbumSync.stopPending = YES;
    defaultAlbumSync.uploadOnGoing = NO;
    
    [defaultAlbumSync.sessionForeground getTasksWithCompletionHandler:^(NSArray *dataTasks, NSArray *uploadTasks, NSArray *downloadTasks) {
        if (uploadTasks && uploadTasks.count > 0) {
            for (NSURLSessionTask *task in uploadTasks) {
                [task cancel];
            }
        }
    }];
    
    /*
    dispatch_async(actionWorkQueue, ^{
        [defaultAlbumSync cancelAllForgroundUploadTasks];
    });
     */
}


- (id)init
{
    if(!(self = [super init]))
        return nil;
    
    /* Init MyLib Functions */
    [self initReachbility];
    [self initUrlSession:15];
    [self initPHPhotoLib];
    
    /* init AlbumsSync */
    self.assetTodoArray = [[NSMutableArray alloc] init];
    self.bgTask = UIBackgroundTaskInvalid;
    self.uploadWorkQueue = dispatch_queue_create(NULL, DISPATCH_QUEUE_SERIAL);
    
    MyInf(@"Init: Albumsync init\n");
    self.retryCount = 0;
    
    self.videoUploadEnabled = YES;
    
    
    // init albums
    
    //    [self reloadAlbums];
    
    
    return self;
}



/////////////// OLD ////////////////////////

- (NSDictionary *)getUploadInfoDict
{
    NSMutableDictionary *statusInfo =
                    [[NSMutableDictionary alloc] initWithDictionary:@{
                                                                      @"status" : self.status == nil ? @"stop" : self.status,
                                                                      @"progress" : [NSNumber numberWithDouble:self.current_progress],
                                                                      @"file_size" : self.current_file_size == nil ? @"" : self.current_file_size,
                                                                      @"file_name" : self.current_file_name == nil ? @"" : self.current_file_name,
                                                                      }];
    if (self.current_asset)
        [statusInfo setObject:self.current_asset forKey:@"asset"];
    return statusInfo;
}

- (void)uploadInfoNotify: (NSString *)status
{
    self.status = status;

    if ([[UIApplication sharedApplication] applicationState] != UIApplicationStateActive) {
        MyInf(@"ignore status notify for background");
        return;
    }
    
    NSDictionary *statusInfo = [self getUploadInfoDict];
    
    if (self.delegate) {
        dispatch_async(dispatch_get_main_queue(), ^{
            @try {
                MyInf(@"send notify: %@", self.status);
                [self.delegate statusUpdateHandler:statusInfo];
            } @catch (NSException *exception) {
                MyErr(@"Name: %@", exception.name);
                MyErr(@"Reason: %@", exception.reason );
                MyErr(@"CallStack: %@", [exception callStackSymbols]);
            } @finally {
                ;
            }
            
        });
    }
}

- (BOOL) scanHandle:(NSString *)str errString:(NSString **)errString
{
    NSString *strJson = base64_decode(str);
    NSMutableDictionary *resDict = json_to_dict(strJson);

    NSString *cmd = [resDict valueForKey:@"C"];
    NSString *url = [resDict valueForKey:@"L"];
    NSString *otp = [resDict valueForKey:@"O"];

    *errString = @"invalid QR code";
    if (!cmd) {
        return NO;
    }
    
    if ([cmd compare:@"b"] == NSOrderedSame) {
        if (!url || !otp) {
            return NO;
        }
        return [self bindServer:url otp:otp errString:errString];
    }else {
        return NO;
    }

    return NO;
}

- (BOOL) bindServer:(NSString *)url otp:(NSString *)otp errString:(NSString **)errString
{
    NSDictionary *resDict = [self sendData:nil params:@{
                                                        @"action" : @"otp",
                                                        @"sub_action" : @"bindDevice",
                                                        @"dev_uuid" : [[[UIDevice currentDevice] identifierForVendor] UUIDString],
                                                        @"dev_name" : [[UIDevice currentDevice] name],
                                                        @"otp" : otp,
                                                        } url:url progressHandler:nil verify:NO error:nil];
    if (!resDict) {
        *errString = @"connect to server error";
        return NO;
    }
    NSString *err = [resDict valueForKey:@"error"];
    NSString *user_name = [resDict valueForKey:@"name"];
    NSString *is_admin_str = [resDict valueForKey:@"is_admin"];
    NSString *user_id = [resDict valueForKey:@"id"];
    NSString *dev_id = [resDict valueForKey:@"dev_id"];
    NSString *token = [resDict valueForKey:@"token"];
    
    if (!err || !user_name || !is_admin_str || !user_id || !dev_id || !token) {
        *errString = @"invalid QR code or QR timeout";
        return NO;
    }
    
    if ([err compare:@"n"] != NSOrderedSame) {
        *errString = @"invalid QR code or QR timeout";
        return NO;
    }
    
    BOOL user_is_admin = [is_admin_str compare:@"y"] == NSOrderedSame;
    
    *errString = [[NSString alloc] initWithFormat:@"bind to %@", user_name];
    
    setup_set_bool(SETUP_KEY_UPLOAD_EN, NO);
    setup_set_bool(SETUP_KEY_IS_BIND, NO);
    setup_set(SETUP_KEY_SERVER_URL, url);
    setup_set(SETUP_KEY_USER_ID, user_id);
    setup_set(SETUP_KEY_DEV_ID, dev_id);
    setup_set(SETUP_KEY_USER_NAME, user_name);
    setup_set(SETUP_KEY_USER_TOKEN, token);
    setup_set_bool(SETUP_KEY_IS_BIND, user_is_admin);
    AlbumSync_reset();
    
    return YES;
}

- (NSUInteger)getAlbumsTodoList
{
    MyInf(@"read todo list from save data ...");
//    self.assetTodoArray = setup_get_MArray(SAVE_KEY_UPLOAD_ASSET_TODO);
    if (self.assetTodoArray.count == 0) {
        MyInf(@"get Albums todo list from server ...");
        NSDictionary *resDict = [self sendData:nil params:@{
                                                            @"action" : @"token",
                                                            @"sub_action" : @"albumsTodo",
                                                            @"max_num" : [[NSString alloc] initWithFormat:@"%d", UPLOAD_ITEM_MAX],
                                                            } url:nil progressHandler:nil verify:YES error:nil];
        
        id tmpArray = [resDict valueForKey:@"localIdentifierArray"];
        if (!tmpArray
            || ![tmpArray isKindOfClass:[NSMutableArray class]]
            || ![tmpArray isKindOfClass:[NSArray class]]) {
            MyErr(@"server return error");
        }else {
            self.assetTodoArray = tmpArray;
//            setup_set(SAVE_KEY_UPLOAD_ASSET_TODO, self.assetTodoArray);
        }
    }

    return self.assetTodoArray.count;
}


- (BOOL)doAlbumsListUpload
{
    BOOL is_AllUpload = setup_get_bool(SETUP_KEY_IS_ALLUPLOAD);
    NSMutableArray *smartCollectionsArray = [[NSMutableArray alloc] init];
    NSMutableArray *setup_sel_albums = setup_get_MArray(SETUP_KEY_SEL_ALBUMS);
    PHFetchResult *userAlbums = [PHCollectionList fetchTopLevelUserCollectionsWithOptions:nil];
    NSMutableDictionary *albumsInfoDict = [[NSMutableDictionary alloc] init];

    MyInf(@"clean Albums ...");
    NSDictionary *resDict = [self sendData:nil params:@{
                                                        @"action" : @"token",
                                                        @"sub_action" : @"albumsClean",
                                                        @"localIdentifier" : @"all",
                                                        } url:nil progressHandler:nil verify:YES error:nil];
    if (!resDict) {
        return NO;
    }

    
    self.photoLibChangePending = NO;
    MyInf(@"load Albums list...\n");
    PHFetchOptions *Options = [[PHFetchOptions alloc] init];
    Options.sortDescriptors = @[[NSSortDescriptor sortDescriptorWithKey:@"creationDate" ascending:NO]];

    if (is_AllUpload) {

        PHFetchResult *smartAlbums = [PHAssetCollection fetchAssetCollectionsWithType:PHAssetCollectionTypeSmartAlbum subtype:PHAssetCollectionSubtypeAlbumRegular options:nil];
        for (int i = 0; i < smartAlbums.count; i++) {
            PHCollection *collection = smartAlbums[i];
            NSString *albumName = collection.localizedTitle;
            if ([albumName compare:LOCAL_STRING(@"ALBUM_RECENTLY_ADDED")] == NSOrderedSame
                || [albumName compare:LOCAL_STRING(@"ALBUM_RECENTLY_DELETED")] == NSOrderedSame
                || [albumName compare:LOCAL_STRING(@"ALBUM_HIDDEN")] == NSOrderedSame) {
                continue;
            }
            [smartCollectionsArray addObject:collection];
        }
    }
    
    for (int i = 0; i < userAlbums.count + smartCollectionsArray.count; i++) {
        PHCollection *collection = i < userAlbums.count ? userAlbums[i] : smartCollectionsArray[i - userAlbums.count];
        NSString *albumName = collection.localizedTitle;
        if (!is_AllUpload && ![setup_sel_albums containsObject:collection.localIdentifier]) {
            MyInf(@"ignore %@", albumName);
            continue;
        }
        PHAssetCollection *assetCollection = (PHAssetCollection *)collection;
        PHFetchResult *assetsFetchResult = [PHAsset fetchAssetsInAssetCollection:assetCollection options:Options];
        for (int j = 0; j < assetsFetchResult.count; j++) {
            PHAsset *asset = assetsFetchResult[j];
            NSMutableDictionary *assetInfoDict = [albumsInfoDict valueForKey:asset.localIdentifier];
            if (!assetInfoDict) {
                assetInfoDict = [[NSMutableDictionary alloc] init];
                [assetInfoDict setObject:[[NSString alloc] initWithFormat:@"%@-%lu", asset.localIdentifier, (unsigned long)[asset.creationDate timeIntervalSince1970]] forKey:@"uuid"];
                [assetInfoDict setObject:asset.localIdentifier forKey:@"localIdentifier"];
                [assetInfoDict setObject:[[NSNumber alloc] initWithUnsignedLong:(unsigned long)[asset.creationDate timeIntervalSince1970]] forKey:@"createTime"];
                NSMutableArray *albums = [[NSMutableArray alloc] init];
                [assetInfoDict setObject:albums forKey:@"albums"];
                [albumsInfoDict setObject:assetInfoDict forKey:asset.localIdentifier];
            }
            NSMutableArray *albums = [assetInfoDict valueForKey:@"albums"];
            [albums addObject:albumName];
        }
    }


    NSArray *albumsInfoArray = [albumsInfoDict allValues];
    for (int i = 0; i < albumsInfoArray.count; i += UPLOAD_ITEM_MAX) {
        if (self.stopPending)
            return NO;
        
        NSArray *uploadArray = [albumsInfoArray subarrayWithRange:NSMakeRange(i, albumsInfoArray.count - i > UPLOAD_ITEM_MAX ? UPLOAD_ITEM_MAX : albumsInfoArray.count - i)];
        NSData *jsonData = [NSJSONSerialization dataWithJSONObject:uploadArray options:NSJSONWritingPrettyPrinted error:nil];
        NSString *jsonStr = [[NSString alloc] initWithData:jsonData encoding:NSUTF8StringEncoding];
        MyInf(@"dump json: %@", jsonStr);
        NSDictionary *resDict = [self sendData:nil params:@{
                                                      @"action" : @"token",
                                                      @"sub_action" : @"albumsUpload",
                                                      @"albumsInfoJson" : jsonStr,
                                                      } url:nil progressHandler:nil verify:YES error:nil];
        if (!resDict) {
            return NO;
        }
        self.current_progress = (double)i/(double)albumsInfoArray.count;
        [self uploadInfoNotify:@"uploading_albums_progress"];

        
    }
    return YES;
}

- (void)continueUpload
{

//    dispatch_async(self.uploadWorkQueue, ^{
        NSString *error = [[NSString alloc] init];
        NSString *lastLocalIdentifier = [[NSString alloc] init];
        NSString *lastUploadOffsetString = [[NSString alloc] init];
        NSString *lastUploadLengthString = [[NSString alloc] init];
        NSString *lastUploadTotalLengthString = [[NSString alloc] init];
        NSUInteger lastUploadOffset;
        NSUInteger lastUploadLength;
        NSUInteger lastUploadTotalLength;

        NSDictionary *retDict = nil;
        PHAsset *lastUploadAsset = nil;

        PHAsset *asset = nil;
        NSData *file_data = nil;
        NSString *file_name = nil;
        NSString *nextLocalIdentifier = nil;
        NSUInteger nextUploadOffset = 0;
        NSUInteger nextUploadLength = 0;
        NSUInteger nextUploadTotalLength = 0;
        
        

        retDict = [self sendData:nil params:@{
                                                          @"action" : @"token",
                                                          @"sub_action" : @"uploadQuery",
                                                          } url:nil progressHandler:nil verify:YES error:&error];

        while (YES) {
            if (self.stopPending) {
                MyInf(@"stop pending, ignore");
                break;
            }

            if (!retDict) {
                MyInf(@"server error, error: %@", error);
                break;
            }
            
            lastLocalIdentifier = [retDict valueForKey:@"lastLocalIdentifier"];
            lastUploadOffsetString = [retDict valueForKey:@"lastUploadOffset"];
            lastUploadLengthString = [retDict valueForKey:@"lastUploadLength"];
            lastUploadTotalLengthString = [retDict valueForKey:@"lastUploadTotalLength"];
            if (!lastLocalIdentifier || !lastUploadOffsetString || !lastUploadLengthString || !lastUploadTotalLengthString) {
                MyInf(@"retDict failed");
                break;
            }
            lastUploadOffset = [lastUploadOffsetString integerValue];
            lastUploadLength = [lastUploadLengthString integerValue];
            lastUploadTotalLength = [lastUploadTotalLengthString integerValue];

            nextUploadOffset = lastUploadOffset + lastUploadLength;
            nextUploadTotalLength = lastUploadTotalLength;
            nextLocalIdentifier = lastLocalIdentifier;

            lastUploadAsset = [self loadAssetWithId:lastLocalIdentifier];
            if (!asset)
                asset = lastUploadAsset;
            if (!lastUploadAsset
                || ([asset.localIdentifier compare:lastLocalIdentifier] != NSOrderedSame)
                || nextUploadOffset >= lastUploadTotalLength) {
                MyInf(@"fetch next");
                asset = nil;
                nextLocalIdentifier = nil;
                file_name = nil;
                file_data = nil;
                nextUploadOffset = 0;
                nextUploadLength = 0;
                nextUploadTotalLength = 0;
                while (self.assetTodoArray.count > 0) {
                    nextLocalIdentifier = self.assetTodoArray[0];
                    [self.assetTodoArray removeObject:nextLocalIdentifier];
                    asset = [self loadAssetWithId:nextLocalIdentifier];
                    if (asset) {
                        break;
                    }else {
                        MyErr(@"unknown localIdentifier, need clean albums on server");
                        MyInf(@"clean Albums ...");
                        [self sendData:nil params:@{
                                                                            @"action" : @"token",
                                                                            @"sub_action" : @"albumsClean",
                                                                            @"localIdentifier" : nextLocalIdentifier
                                                                            } url:nil progressHandler:nil verify:YES error:nil];
                    }
                }
                if (!asset) {
                    MyInf(@"TODOArray = 0, complete");
                    break;
                }
            }

            if (!file_name || !file_data) {
                NSArray *resources = [PHAssetResource assetResourcesForAsset:asset];
                file_name = ((PHAssetResource*)resources[0]).originalFilename;
                MyDbg(@"doUpload: file_name: %@, id=%@\n", file_name, asset.localIdentifier);

                NSMutableDictionary *info = [self fechPHAssetData:asset];
                if (!info) {
                    MyErr(@"fetch asset data error");
                    asset = nil;
                    file_name = nil;
                    file_data = nil;
                    nextUploadOffset = 0;
                    nextUploadLength = 0;
                    nextUploadTotalLength = 0;
                    continue;
                }
                file_data = [info objectForKey:@"data"];
                nextUploadTotalLength = [[info objectForKey:@"data_length"] unsignedIntegerValue];

                self.current_asset = asset;
                self.current_file_name = file_name;
                self.current_file_size = [NSString stringWithFormat:@"%.3f MB", (double)nextUploadTotalLength/(1024*1024)];
                self.current_progress = (double)nextUploadOffset/(double)nextUploadTotalLength;
                [self uploadInfoNotify:@"uploading_file"];
            }
            
            self.current_progress = (double)nextUploadOffset/(double)nextUploadTotalLength;
            [self uploadInfoNotify:@"uploading_progress"];

            nextUploadLength = UPLOAD_MAX_LENGTH;
            if (nextUploadOffset + nextUploadLength > nextUploadTotalLength)
                nextUploadLength = nextUploadTotalLength - nextUploadOffset;
            if (nextUploadTotalLength <= 0) {
                MyErr(@"currentUploadLength <= 0, should not hit here\n");
                asset = nil;
                file_name = nil;
                file_data = nil;
                nextUploadOffset = 0;
                nextUploadLength = 0;
                nextUploadTotalLength = 0;
                continue;
            }
            
            
            NSData *data = [file_data subdataWithRange:NSMakeRange(nextUploadOffset, nextUploadLength)];
            NSString *file_type = asset.mediaType == PHAssetMediaTypeImage ? @"image" :
            asset.mediaType == PHAssetMediaTypeVideo ? @"video" : @"unknown";

            retDict = [self sendData:data params:@{
                                                              @"action" : @"token",
                                                              @"sub_action" : @"uploadChunk",
                                                              @"file_type" : file_type,
                                                              @"file_offset" : [NSNumber numberWithUnsignedInteger:nextUploadOffset],
                                                              @"file_length" : [NSNumber numberWithUnsignedInteger:nextUploadTotalLength],
                                                              @"chunk_length" : [NSNumber numberWithUnsignedInteger:nextUploadLength],
                                                              @"localIdentifier" : nextLocalIdentifier,
                                                              @"file_name" : file_name,
                                                              } url:nil
                     progressHandler:^(int64_t totalBytesSent){
                         self.current_progress = (double)(nextUploadOffset+totalBytesSent)/(double)nextUploadTotalLength;
                         [self uploadInfoNotify:@"uploading_progress"];
                     }
                     verify:YES error:&error];

            
        }
//    });
}


- (NSDictionary *)sendData:(NSData *)data
                    params:(NSDictionary *)params
                       url:(NSString *)url
           progressHandler:(void (^)(int64_t totalBytesSent))progressHandler
                    verify:(BOOL)verify
                     error:(NSString **)error
{
    __block NSData *retData = nil;
    NSDictionary *retDict = nil;
    if (error)
        *error = nil;
    
    dispatch_group_t group = dispatch_group_create();
    dispatch_group_enter(group);

    if (!url)
        url = self.uploadServerURLString;
    NSMutableDictionary *urlParams = [[NSMutableDictionary alloc] initWithDictionary:params];
    if (verify) {
        NSUInteger tokenTime = MyLib_UnixTimeUs()/1000 + self.serverTimeDelta;
        NSString *tokenRaw = [[NSString alloc] initWithFormat:@"%@%ld", setup_get_string(SETUP_KEY_USER_TOKEN), tokenTime];
        NSString *token = MyLib_DataMD5([tokenRaw dataUsingEncoding:NSUTF8StringEncoding]);
        [urlParams setObject:token forKey:@"token"];
        [urlParams setObject:[[NSString alloc] initWithFormat:@"%ld", tokenTime] forKey:@"tokenTime"];
        [urlParams setObject:setup_get_string(SETUP_KEY_USER_ID) forKey:@"user_id"];
        [urlParams setObject:setup_get_string(SETUP_KEY_DEV_ID) forKey:@"dev_id"];
    }

    dispatch_async(self.asyncWorkQueue, ^{
        [self postDataForeground:data urlString:url urlParams:urlParams completionHandler:^(NSData *respData, NSURLResponse *response, NSError *error){
            if (!error)
                retData = respData;
            dispatch_group_leave(group);
        }
                 progressHandler:progressHandler
         ];
    });
    dispatch_group_wait(group, DISPATCH_TIME_FOREVER);
    
    if (!retData)
        self.uploadErrorPending = YES;

    if (retData) {
        NSString *jsonStr = [[NSString alloc] initWithData:retData encoding:NSUTF8StringEncoding];
        retDict = json_to_dict(jsonStr);
        if (verify) {
            NSString *err = [retDict valueForKey:@"error"];
            NSString *errString = [retDict valueForKey:@"errString"];
            NSString *serverTimeString = [retDict valueForKey:@"serverTime"];
            NSString *tokenTimeString = [retDict valueForKey:@"tokenTime"];
            NSString *token = [retDict valueForKey:@"token"];
            
            if (error)
                *error = err;
            if (!err || !token || !tokenTimeString) {
                MyInf(@"token/err miss");
                self.uploadErrorPending = YES;
                return nil;
            }
            NSString *tokenRawLocal = [[NSString alloc] initWithFormat:@"%@%@", setup_get_string(SETUP_KEY_USER_TOKEN), tokenTimeString];
            NSString *tokenLocal = MyLib_DataMD5([tokenRawLocal dataUsingEncoding:NSUTF8StringEncoding]);
            if ([tokenLocal compare:token] != NSOrderedSame) {
                MyInf(@"token error");
                self.uploadErrorPending = YES;
                return nil;
            }
            if ([err compare:@"n"] != NSOrderedSame) {
                MyInf(@"error: %@", errString);
                self.uploadErrorPending = YES;
                return nil;
            }

            if (serverTimeString) {
                NSUInteger serverTime = [serverTimeString integerValue];
                self.serverTimeDelta = serverTime - MyLib_UnixTimeUs()/1000;
            }
        }
    }

    return retDict;
}

- (void)startUploadBgTask
{
    if (self.bgTask == UIBackgroundTaskInvalid) {
        self.bgTask = [[UIApplication sharedApplication] beginBackgroundTaskWithName:@"Albums Sync BG TAsk" expirationHandler:^{
            // Clean up any unfinished task business by marking where you
            // stopped or ending the task outright.]
            MyInf(@"expirationHandle ...");
            MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
            [[UIApplication sharedApplication] endBackgroundTask:self.bgTask];
            self.bgTask = UIBackgroundTaskInvalid;
        }];
        MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
    }
}

- (void)stopUploadBgTask
{
    if (self.bgTask != UIBackgroundTaskInvalid) {
            MyInf(@"stop ...");
            MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
            [[UIApplication sharedApplication] endBackgroundTask:self.bgTask];
            self.bgTask = UIBackgroundTaskInvalid;
        }
}

#pragma mark - CLLocationManager Delegate

- (void)locationManager:(CLLocationManager *)manager didUpdateLocations:(NSArray *)locations{
    MyInf(@"locationManager didUpdateLocations: %@",locations);
    AlbumSync_Start();
    /*
    for (int i = 0; i < locations.count; i++) {
        CLLocation * newLocation = [locations objectAtIndex:i];
 //       CLLocationCoordinate2D theLocation = newLocation.coordinate;
 //       CLLocationAccuracy theAccuracy = newLocation.horizontalAccuracy;

        MyDbg(@"new location: %@", newLocation);
    }
     */
}

#pragma mark - PHPhotoLibraryChangeObserver

- (void)photoLibraryDidChange:(PHChange *)changeInstance {
    MyInf(@"PhotoLib changed, %@", changeInstance);
    self.photoLibChangePending = YES;
    if (!self.photoLibChangePending)
        AlbumSync_Start();
}

- (void)updateInterfaceWithReachability:(Reachability *)reachability
{
    [super updateInterfaceWithReachability:reachability];
    self.reachabilityChangePending = YES;
        AlbumSync_Start();
}


@end

