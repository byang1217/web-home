//
//  MyLib.m
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "AppDelegate.h"
#import "MyLib.h"

@interface MyLib () <NSURLSessionDelegate, NSURLSessionTaskDelegate, NSURLSessionDataDelegate, CLLocationManagerDelegate, PHPhotoLibraryChangeObserver>

@property NSURLSessionTask *currentForegroundTask;
@property void (^currentForegroundTaskProgressHandler)(int64_t totalBytesSent);

@property (nonatomic) CLLocationManager * LocationManager_update;
@property (nonatomic) CLLocationManager * LocationManager_monitor;
@property BOOL deferringUpdates;

@property (nonatomic) Reachability *internetReachability;

@end

@implementation MyLib


/*
- (void)initURLSessionConfg:(NSURLSessionConfiguration *)config name:(NSString *)name
{
#ifdef MYLIB_CAP_BACKGROUD_UPLOAD
    config = [NSURLSessionConfiguration backgroundSessionConfigurationWithIdentifier:name];
    //configuration.discretionary = YES; //TODO: add setting option. it will wait for wifi
    config.discretionary = NO; //TODO: add setting option. it will wait for wifi
    config.sessionSendsLaunchEvents = YES;
#else
    config = [NSURLSessionConfiguration defaultSessionConfiguration];
#endif
//    config.HTTPMaximumConnectionsPerHost = 1;
    config.allowsCellularAccess = NO; //TODO: add setting option
    config.timeoutIntervalForResource = (15*60);
//    config.timeoutIntervalForRequest = (60);
}

- (BOOL)resetURLSession
{
    self.isURLSessionReady = NO;

    if (self.session == self.sessionPing) {
        self.session = nil;
        [self.sessionPing invalidateAndCancel];
        if (self.isURLSessionPangInvalid) {
            self.sessionPang = [NSURLSession sessionWithConfiguration:self.sessionConfigPang delegate:self delegateQueue:nil];
            self.session = self.sessionPang;
            self.isURLSessionPangInvalid = NO;
            self.isURLSessionReady = YES;
            return YES;
        }
    }
        
    if (self.session == self.sessionPang) {
        self.session = nil;
        [self.sessionPang invalidateAndCancel];
        if (self.isURLSessionPingInvalid) {
            self.sessionPing = [NSURLSession sessionWithConfiguration:self.sessionConfigPing delegate:self delegateQueue:nil];
            self.session = self.sessionPing;
            self.isURLSessionPingInvalid = NO;
            self.isURLSessionReady = YES;
            return YES;
        }
    }

    if (self.session == nil) {
        if (self.isURLSessionPingInvalid) {
            self.sessionPing = [NSURLSession sessionWithConfiguration:self.sessionConfigPing delegate:self delegateQueue:nil];
            self.session = self.sessionPing;
            self.isURLSessionPingInvalid = NO;
            self.isURLSessionReady = YES;
            return YES;
        }
        if (self.isURLSessionPangInvalid) {
            self.sessionPang = [NSURLSession sessionWithConfiguration:self.sessionConfigPang delegate:self delegateQueue:nil];
            self.session = self.sessionPang;
            self.isURLSessionPangInvalid = NO;
            self.isURLSessionReady = YES;
            return YES;
        }
    }
    return NO;
}

- (void)initPingPangSession
{
    self.isURLSessionPangInvalid = NO;
    self.isURLSessionPingInvalid = NO;
    NSString *pingName = [NSString stringWithFormat:@"%@-ping",[[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleName"]];
    NSString *pangName = [NSString stringWithFormat:@"%@-pang",[[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleName"]];
    [self initURLSessionConfg:self.sessionConfigPing name:pingName];
    [self initURLSessionConfg:self.sessionConfigPang name:pangName];
    self.sessionPing = [NSURLSession sessionWithConfiguration:self.sessionConfigPing delegate:self delegateQueue:nil];
    self.sessionPang = [NSURLSession sessionWithConfiguration:self.sessionConfigPang delegate:self delegateQueue:nil];
    [self.sessionPing invalidateAndCancel];
    [self.sessionPang invalidateAndCancel];
}
*/

- (void)backgroundSessionReady
{
    MyErr(@"please implement it");
}

- (void)initBackgroundSession:(NSString *)name timeout:(NSInteger)timeout
{
    MyInf(@"init background session, name:%@, timeout=%ld \n", name, (long)timeout);
//    NSURLSessionConfiguration *configBackground = [NSURLSessionConfiguration backgroundSessionConfigurationWithIdentifier:@"background-006"];
    NSURLSessionConfiguration *configBackground = [NSURLSessionConfiguration backgroundSessionConfigurationWithIdentifier:name];

    configBackground.discretionary = NO; //TODO: add setting option. it will wait for wifi
    configBackground.sessionSendsLaunchEvents = YES;
    configBackground.HTTPMaximumConnectionsPerHost = 1;
    configBackground.allowsCellularAccess = NO; //TODO: add setting option
    configBackground.timeoutIntervalForResource = timeout;
    self.sessionBackground = [NSURLSession sessionWithConfiguration:configBackground delegate:self delegateQueue:nil];
    self.isBackgroundSessionReady = YES;

}

- (void)initUrlSession:(NSInteger)timeout
{
    MyInf(@"init foreground session\n");
    NSURLSessionConfiguration *configForeground = [NSURLSessionConfiguration defaultSessionConfiguration];
//    configForeground.HTTPMaximumConnectionsPerHost = 1;
    configForeground.timeoutIntervalForRequest = timeout;
    configForeground.allowsCellularAccess = NO; //TODO: add setting option
    self.sessionForeground = [NSURLSession sessionWithConfiguration:configForeground delegate:self delegateQueue:nil];
    
//    [self initBackgroundSession];
    //    [self invalidBackgroundSession];
}

- (void)initPHPhotoLib
{
    [[PHPhotoLibrary sharedPhotoLibrary] registerChangeObserver:self];
}

- (id)init
{
    if(!(self = [super init]))
        return nil;
    
    //        NSString *BackgroundSessionName = [NSString stringWithFormat:@"%@-background-upload-%@",[[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleName"],MyLib_UnixTimeString()];
    MyInf(@"Init: MyLib init\n");
    self.asyncWorkQueue = dispatch_queue_create(NULL, DISPATCH_QUEUE_SERIAL);

    self.allAssetArray = [[NSMutableArray alloc] init];
    self.imageAssetArray = [[NSMutableArray alloc] init];
    self.videoAssetArray = [[NSMutableArray alloc] init];
    self.SmartAlbumsDict = [[NSMutableDictionary alloc] init];
    self.UserAlbumsDict = [[NSMutableDictionary alloc] init];
    return self;
}

/* Network */
- (void)initReachbility
{
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(reachabilityChanged:) name:kReachabilityChangedNotification object:nil];
    self.internetReachability = [Reachability reachabilityForInternetConnection];
    [self.internetReachability startNotifier];
    [self updateInterfaceWithReachability:self.internetReachability];
}

/*!
 * Called by Reachability whenever status changes.
 */
- (void) reachabilityChanged:(NSNotification *)note
{
    Reachability* curReach = [note object];
    NSParameterAssert([curReach isKindOfClass:[Reachability class]]);
    [self updateInterfaceWithReachability:curReach];
}


- (void)updateInterfaceWithReachability:(Reachability *)reachability
{
    NetworkStatus netStatus = [reachability currentReachabilityStatus];

    self.isNetWorkWiFi = NO;
    self.isNetWorkWWAN = NO;
    switch (netStatus)
    {
        case NotReachable:        {
            MyInf(@"network not available");
            break;
        }
        case ReachableViaWWAN:        {
            MyInf(@"network = WWAN");
            self.isNetWorkWWAN = YES;
            break;
        }
        case ReachableViaWiFi:        {
            MyInf(@"network = WIFI");
            self.isNetWorkWiFi = YES;
            break;
        }
        default: {
            MyErr(@"network unknown");
            break;
        }
    }
}

/*
    Photo Functions 
 */

#pragma mark - PHPhotoLibraryChangeObserver

- (void)photoLibraryDidChange:(PHChange *)changeInstance {
    MyInf(@"PhotoLib changed, %@", changeInstance);
    /*
     Change notifications may be made on a background queue. Re-dispatch to the
     main queue before acting on the change as we'll be updating the UI.
     */
    /*
    dispatch_async(dispatch_get_main_queue(), ^{
        // Loop through the section fetch results, replacing any fetch results that have been updated.
        NSMutableArray *updatedSectionFetchResults = [self.sectionFetchResults mutableCopy];
        __block BOOL reloadRequired = NO;
        
        [self.sectionFetchResults enumerateObjectsUsingBlock:^(PHFetchResult *collectionsFetchResult, NSUInteger index, BOOL *stop) {
            PHFetchResultChangeDetails *changeDetails = [changeInstance changeDetailsForFetchResult:collectionsFetchResult];
            
            if (changeDetails != nil) {
                [updatedSectionFetchResults replaceObjectAtIndex:index withObject:[changeDetails fetchResultAfterChanges]];
                reloadRequired = YES;
            }
        }];
        
        if (reloadRequired) {
            self.sectionFetchResults = updatedSectionFetchResults;
            [self.tableView reloadData];
        }
        
    });
     */
}


- (PHAsset *) loadAssetWithId:(NSString *)LocalIdentifier
{
    return [PHAsset fetchAssetsWithLocalIdentifiers:@[LocalIdentifier] options:nil].firstObject;
}

- (NSMutableDictionary *)fechPHAssetData:(PHAsset *)asset
{
    __block NSMutableDictionary *result = [[NSMutableDictionary alloc] init];
    dispatch_group_t group = dispatch_group_create();
    
    [result setObject:asset.localIdentifier forKey:@"localIdentifier"];
    if (asset.mediaType == PHAssetMediaTypeImage) {
        PHImageRequestOptions *Options = [[PHImageRequestOptions alloc] init];
        Options.networkAccessAllowed = YES;
        Options.deliveryMode = PHImageRequestOptionsDeliveryModeHighQualityFormat;

        dispatch_group_enter(group);
        dispatch_async(dispatch_get_global_queue(DISPATCH_QUEUE_PRIORITY_DEFAULT, 0), ^{
            [[PHImageManager defaultManager]
             requestImageDataForAsset:asset
             options:Options
             resultHandler:^(NSData *imageData, NSString *dataUTI, UIImageOrientation orientation, NSDictionary *info) {
                 if ([[info objectForKey:PHImageCancelledKey] boolValue]) {
                     MyErr(@"cancel image data request\n");
                     result = nil;
                     dispatch_group_leave(group);
                 }else if ([info objectForKey:PHImageErrorKey]) {
                     MyErr(@"Image data request Error\n");
                     result = nil;
                     dispatch_group_leave(group);
                 }else if ([[info objectForKey:PHImageResultIsDegradedKey] boolValue]) {
//                     MyDbg(@"Image data request low quality\n");
                 }else if ([[info objectForKey:PHImageResultIsInCloudKey] boolValue]) {
                     MyErr(@"Image data request in cloud. it should not happen since we enabled the network option\n");
                     result = nil;
                     dispatch_group_leave(group);
                 }else {
                     if (imageData == nil)
                         MyErr(@"error, info=%@", info);
                     [result setObject:imageData forKey:@"data"];
                     [result setObject:[NSNumber numberWithUnsignedInteger:imageData.length] forKey:@"data_length"];
                     dispatch_group_leave(group);
                 }
             }
             ];
        });
        dispatch_group_wait(group, DISPATCH_TIME_FOREVER);
    }else if (asset.mediaType == PHAssetMediaTypeVideo) {
        PHVideoRequestOptions *Options = [[PHVideoRequestOptions alloc] init];
        Options.networkAccessAllowed = YES;
        Options.deliveryMode = PHVideoRequestOptionsDeliveryModeHighQualityFormat;
        
        dispatch_group_enter(group);
        dispatch_async(dispatch_get_global_queue(DISPATCH_QUEUE_PRIORITY_DEFAULT, 0), ^{
            [[PHImageManager defaultManager]
             requestAVAssetForVideo:asset
             options:Options
             resultHandler:^(AVAsset *avasset, AVAudioMix *audioMix, NSDictionary *info) {
                 if ([[info objectForKey:PHImageCancelledKey] boolValue]) {
                     MyErr(@"cancel video data request\n");
                     result = nil;
                     dispatch_group_leave(group);
                 }else if ([info objectForKey:PHImageErrorKey]) {
                     MyErr(@"video data request Error\n");
                     result = nil;
                     dispatch_group_leave(group);
                 }else if ([[info objectForKey:PHImageResultIsDegradedKey] boolValue]) {
                     MyDbg(@"video data request low quality\n");
                 }else if ([[info objectForKey:PHImageResultIsInCloudKey] boolValue]) {
                     MyErr(@"video data request in cloud. it should not happen since we enabled the network option\n");
                     result = nil;
                     dispatch_group_leave(group);
                 }else {
                     MyDbg(@"video data request High quality\n");
                     NSError *error;
                     NSURL *localVideoUrl = [(AVURLAsset *)avasset URL];
                     NSData *VideoData = [NSData dataWithContentsOfURL:localVideoUrl options:(NSDataReadingUncached|NSDataReadingMappedIfSafe) error:&error];
                     if (error) {
                         MyErr(@"error=%@", error);
                     }
                     [result setObject:VideoData forKey:@"data"];
                     [result setObject:[NSNumber numberWithUnsignedInteger:VideoData.length] forKey:@"data_length"];
                     dispatch_group_leave(group);
                 }
             }
             ];
        });
        dispatch_group_wait(group, DISPATCH_TIME_FOREVER);
    }else {
        result = nil;
    }
    return result;
}

- (void) clearAlbums
{
    self.allAssetArray = [[NSMutableArray alloc] init];
    self.imageAssetArray = [[NSMutableArray alloc] init];
    self.videoAssetArray = [[NSMutableArray alloc] init];
    self.SmartAlbumsDict = [[NSMutableDictionary alloc] init];
    self.UserAlbumsDict = [[NSMutableDictionary alloc] init];
}

- (void) loadAlbums:(NSDate *)imageFromDate videoFromDate:(NSDate *)videoFromDate
{
    [self clearAlbums];

    BOOL imageDone = NO;
    BOOL videoDone = NO;
    PHFetchOptions *Options = [[PHFetchOptions alloc] init];

    Options.sortDescriptors = @[[NSSortDescriptor sortDescriptorWithKey:@"creationDate" ascending:NO]];
//    Options.sortDescriptors = @[[NSSortDescriptor sortDescriptorWithKey:@"modificationDate" ascending:NO]];

    imageDone = NO;
    videoDone = NO;
    PHFetchResult *result = [PHAsset fetchAssetsWithOptions:Options];
    for (int i = 0; i < result.count; i++) {
        PHAsset *asset = result[i];
        if (!imageDone && [asset.creationDate compare:imageFromDate] != NSOrderedDescending)
            imageDone = YES;
        if (!videoDone && [asset.creationDate compare:videoFromDate] != NSOrderedDescending)
            videoDone = YES;
        if (imageDone && videoDone)
            break;
        if (!imageDone && asset.mediaType == PHAssetMediaTypeImage) {
            [self.imageAssetArray addObject:asset];
        }else if (!videoDone && asset.mediaType == PHAssetMediaTypeVideo) {
            [self.videoAssetArray addObject:asset];
        }else {
            continue;
        }
    }
    [self.imageAssetArray sortUsingComparator:^NSComparisonResult(id left, id right) {
        return [((PHAsset *)left).creationDate compare:((PHAsset *)right).creationDate];
    }];
    [self.videoAssetArray sortUsingComparator:^NSComparisonResult(id left, id right) {
        return [((PHAsset *)left).creationDate compare:((PHAsset *)right).creationDate];
    }];
    [self.allAssetArray addObjectsFromArray:self.imageAssetArray];
    [self.allAssetArray addObjectsFromArray:self.videoAssetArray];
    [self.allAssetArray sortUsingComparator:^NSComparisonResult(id left, id right) {
        return [((PHAsset *)left).creationDate compare:((PHAsset *)right).creationDate];
    }];


    PHFetchResult *smartAlbums = [PHAssetCollection fetchAssetCollectionsWithType:PHAssetCollectionTypeSmartAlbum subtype:PHAssetCollectionSubtypeAlbumRegular options:nil];
    for (int i = 0; i < smartAlbums.count; i++) {
        PHCollection *collection = smartAlbums[i];
        PHAssetCollection *assetCollection = (PHAssetCollection *)collection;
        PHFetchResult *assetsFetchResult = [PHAsset fetchAssetsInAssetCollection:assetCollection options:Options];
        NSString *key = collection.localizedTitle;
        NSMutableArray *assetsArray = [[NSMutableArray alloc] init];
        imageDone = NO;
        videoDone = NO;
        for (int i = 0; i < assetsFetchResult.count; i++) {
            PHAsset *asset = assetsFetchResult[i];
            if (!imageDone && [asset.creationDate compare:imageFromDate] != NSOrderedDescending)
                imageDone = YES;
            if (!videoDone && [asset.creationDate compare:videoFromDate] != NSOrderedDescending)
                videoDone = YES;
            if (imageDone && videoDone)
                break;
            if (!imageDone && asset.mediaType == PHAssetMediaTypeImage) {
                [assetsArray addObject:asset];
            }else if (!videoDone && asset.mediaType == PHAssetMediaTypeVideo) {
                [assetsArray addObject:asset];
            }else {
                continue;
            }
        }
        [assetsArray sortUsingComparator:^NSComparisonResult(id left, id right) {
            return [((PHAsset *)left).creationDate compare:((PHAsset *)right).creationDate];
        }];
        [self.SmartAlbumsDict setObject:assetsArray forKey:key];
    }

    PHFetchResult *userAlbums = [PHCollectionList fetchTopLevelUserCollectionsWithOptions:nil];
    for (int i = 0; i < userAlbums.count; i++) {
        PHCollection *collection = userAlbums[i];
        PHAssetCollection *assetCollection = (PHAssetCollection *)collection;
        PHFetchResult *assetsFetchResult = [PHAsset fetchAssetsInAssetCollection:assetCollection options:Options];
        NSString *key = collection.localizedTitle;
        NSMutableArray *assetsArray = [[NSMutableArray alloc] init];
        imageDone = NO;
        videoDone = NO;
        for (int i = 0; i < assetsFetchResult.count; i++) {
            PHAsset *asset = assetsFetchResult[i];
            if (!imageDone && [asset.creationDate compare:imageFromDate] != NSOrderedDescending)
                imageDone = YES;
            if (!videoDone && [asset.creationDate compare:videoFromDate] != NSOrderedDescending)
                videoDone = YES;
            if (imageDone && videoDone)
                break;
            if (!imageDone && asset.mediaType == PHAssetMediaTypeImage) {
                [assetsArray addObject:asset];
            }else if (!videoDone && asset.mediaType == PHAssetMediaTypeVideo) {
                [assetsArray addObject:asset];
            }else {
                continue;
            }
        }
        [assetsArray sortUsingComparator:^NSComparisonResult(id left, id right) {
            return [((PHAsset *)left).creationDate compare:((PHAsset *)right).creationDate];
        }];
        [self.UserAlbumsDict setObject:assetsArray forKey:key];
    }
}

/*
 Upload Functions
 */
/*
- (void)cancelAllTask
{
    self.isURLSessionReady = NO;
    dispatch_group_t group = dispatch_group_create();
    dispatch_group_enter(group);
    [self.session getTasksWithCompletionHandler:^(NSArray *dataTasks, NSArray *uploadTasks, NSArray *downloadTasks) {
        self.isURLSessionReady = YES;
        if (dataTasks && dataTasks.count > 0) {
            for (NSURLSessionTask *task in dataTasks) {
                MyErr(@"cancel task: %@", task);
                self.isURLSessionReady = NO;
                [task cancel];
            }
        }
        if (uploadTasks && uploadTasks.count > 0) {
            for (NSURLSessionTask *task in uploadTasks) {
                MyErr(@"cancel task: %@", task);
                self.isURLSessionReady = NO;
                [task cancel];
            }
        }
        if (downloadTasks && downloadTasks.count > 0) {
            for (NSURLSessionTask *task in downloadTasks) {
                MyErr(@"cancel task: %@", task);
                self.isURLSessionReady = NO;
                [task cancel];
            }
        }
        dispatch_group_leave(group);
    }];
    dispatch_group_wait(group, DISPATCH_TIME_FOREVER);
}
*/

- (void)invalidBackgroundSession
{
    self.isBackgroundSessionReady = NO;
    [self.sessionBackground invalidateAndCancel];
}

- (void)cancelAllBackgroundTasks
{
    self.isBackgroundSessionReady = NO;
    dispatch_group_t group = dispatch_group_create();
    
    while(!self.isBackgroundSessionReady) {
        MyInf(@"try to cancel all tasks ...");
        dispatch_group_enter(group);
        [self.sessionBackground getAllTasksWithCompletionHandler:^(NSArray *tasksArray) {
            if (!tasksArray || tasksArray.count <= 0) {
                self.isBackgroundSessionReady = YES;
            }else {
                for (NSURLSessionTask *task in tasksArray) {
                    MyErr(@"cancel task: %@", task);
                    [task cancel];
                }
            }
            dispatch_group_leave(group);
        }];
        MyInf(@"wait and check tasks ...");
        dispatch_group_wait(group, DISPATCH_TIME_FOREVER);
    }
    MyInf(@"cancel tasks done");
}

- (void)cancelAllForgroundUploadTasks
{
    __block BOOL is_stopped = NO;
    
    MyInf(@"cancel all foreground tasks");
    
    dispatch_group_t group = dispatch_group_create();
    dispatch_group_enter(group);
    dispatch_async(self.asyncWorkQueue, ^{
        while (!is_stopped) {
            [self.sessionForeground getTasksWithCompletionHandler:^(NSArray *dataTasks, NSArray *uploadTasks, NSArray *downloadTasks) {
                if (uploadTasks && uploadTasks.count > 0) {
                    for (NSURLSessionTask *task in uploadTasks) {
                        [task cancel];
                    }
                    usleep(100);
                }else {
                    is_stopped = YES;
                }
            }];
        }
        dispatch_group_leave(group);
    });
    dispatch_group_wait(group, DISPATCH_TIME_FOREVER);
}

- (NSMutableData *)bodyWithData:(NSData *)data urlParams:(NSDictionary *)urlParams boundary:(NSString *)boundary
{
    NSMutableData *body = [NSMutableData data];
    
    if (data) {
        [body appendData:[[NSString stringWithFormat:@"--%@\r\n", boundary] dataUsingEncoding:NSUTF8StringEncoding]];
        [body appendData:[[NSString stringWithFormat:@"Content-Disposition: form-data; name=\"userfile\"; filename=\"%@\"\r\n", @"upload.tmp"] dataUsingEncoding:NSUTF8StringEncoding]];
        [body appendData:[[NSString stringWithFormat:@"Content-Type: %@\r\n\r\n", @"application/octet-stream"] dataUsingEncoding:NSUTF8StringEncoding]];
        [body appendData:data];
        [body appendData:[@"\r\n" dataUsingEncoding:NSUTF8StringEncoding]];
        
        [body appendData:[[NSString stringWithFormat:@"--%@\r\n", boundary] dataUsingEncoding:NSUTF8StringEncoding]];
        [body appendData:[[NSString stringWithFormat:@"Content-Disposition: form-data; name=\"file_md5\"\r\n\r\n%@\r\n", MyLib_DataMD5(data)] dataUsingEncoding:NSUTF8StringEncoding]];
    }
    [urlParams enumerateKeysAndObjectsUsingBlock:^(id key, id obj, BOOL *stop) {
        [body appendData:[[NSString stringWithFormat:@"--%@\r\n", boundary] dataUsingEncoding:NSUTF8StringEncoding]];
        [body appendData:[[NSString stringWithFormat:@"Content-Disposition: form-data; name=\"%@\"\r\n\r\n%@\r\n", key, obj] dataUsingEncoding:NSUTF8StringEncoding]];
    }];
    
    [body appendData:[[NSString stringWithFormat:@"--%@--\r\n", boundary] dataUsingEncoding:NSUTF8StringEncoding]];
    
    return body;
}

- (BOOL)postDataBackground:(NSData *)data urlString:(NSString *)urlString urlParams:(NSDictionary *)urlParams
{
    NSURL *uploadURL = [NSURL URLWithString:urlString];
    NSString *boundary = [NSString stringWithFormat:@"Boundary-%@", [[NSUUID UUID] UUIDString]];;
    NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:uploadURL];
  
    [request setHTTPMethod:@"POST"];
    [request addValue:[NSString stringWithFormat:@"multipart/form-data; boundary=%@", boundary] forHTTPHeaderField:@"Content-Type"];
    
    NSMutableData *body = [self bodyWithData:data urlParams:urlParams boundary:boundary];

    NSString *docDir = [NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES) objectAtIndex:0];
    NSString *bodyPath = [NSString stringWithFormat:@"%@/%@", docDir, @"uploadBody.data"];
    NSURL *bodyURL = [NSURL fileURLWithPath:bodyPath];
    [body writeToURL:bodyURL atomically:NO];

    NSURLSessionUploadTask *task = [self.sessionBackground uploadTaskWithRequest:request fromFile:bodyURL];
    MyInf(@"background post resume");
    [task resume];
    return YES;
}

- (BOOL)postDataForeground:(NSData *)data urlString:(NSString *)urlString urlParams:(NSDictionary *)urlParams completionHandler:(void (^)(NSData *data, NSURLResponse *response, NSError *error))completionHandler progressHandler:(void (^)(int64_t totalBytesSent))progressHandler
{
    NSURL *uploadURL = [NSURL URLWithString:urlString];
    NSString *boundary = [NSString stringWithFormat:@"Boundary-%@", [[NSUUID UUID] UUIDString]];;
    NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:uploadURL];
    
    [request setHTTPMethod:@"POST"];
    [request addValue:[NSString stringWithFormat:@"multipart/form-data; boundary=%@", boundary] forHTTPHeaderField:@"Content-Type"];
    
    NSMutableData *body = [self bodyWithData:data urlParams:urlParams boundary:boundary];
    
    NSURLSessionUploadTask *task = [self.sessionForeground uploadTaskWithRequest:request fromData:body completionHandler:^(NSData *data, NSURLResponse *response, NSError *error){
        completionHandler(data, response, error);
    }];
    self.currentForegroundTask = task;
    self.currentForegroundTaskProgressHandler = progressHandler;

    MyInf(@"foreground post resume");
    [task resume];
    return YES;
}

- (BOOL)getDataBackground:(NSString *)urlString urlParams:(NSDictionary *)urlParams
{
    __block NSString *fullUrlString = urlString;
    __block int count = 0;
    [urlParams enumerateKeysAndObjectsUsingBlock:^(id key, id obj, BOOL *stop) {
        if (count == 0) {
            fullUrlString = [NSString stringWithFormat:@"%@?%@=%@", fullUrlString, key, obj];
        }else {
            fullUrlString = [NSString stringWithFormat:@"%@&%@=%@", fullUrlString, key, obj];
        }
        count ++;
    }];

    NSURLSessionDataTask *task = [self.sessionBackground dataTaskWithURL:[NSURL URLWithString:fullUrlString]];
    MyInf(@"background get resume");
    [task resume];
    return YES;
}

#pragma mark - NSURLSession
- (void)URLSession:(NSURLSession *)session
              task:(NSURLSessionTask *)task
   didSendBodyData:(int64_t)bytesSent
    totalBytesSent:(int64_t)totalBytesSent
totalBytesExpectedToSend:(int64_t)totalBytesExpectedToSend
{
    MyDbg(@"didSendBodyData, request=%@ totalBytesSent=%lld, totalBytesExpectedToSend=%lld\n", task.currentRequest, totalBytesSent, totalBytesExpectedToSend);

    if (self.currentForegroundTask == task && self.currentForegroundTaskProgressHandler && totalBytesExpectedToSend > 0)
        self.currentForegroundTaskProgressHandler(totalBytesSent);
}

- (void)URLSession:(NSURLSession *)session task:(NSURLSessionTask *)task didCompleteWithError:(NSError *)error
{
    MyInf(@"request=%@ error=%@\n", task.currentRequest, error);
}

- (void)URLSession:(NSURLSession *)session dataTask:(NSURLSessionDataTask *)dataTask didReceiveData:(NSData *)data
{
    MyInf(@"request=%@ data=%@\n", dataTask.currentRequest, [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding]);
}

- (void)URLSession:(NSURLSession *)session didBecomeInvalidWithError:(NSError *)error
{
    MyInf(@"error=%@\n", error);
    /*
    dispatch_async(dispatch_get_main_queue(), ^{
        [self initBackgroundSession];
        [self backgroundSessionReady];
    });
     */

}

- (void)URLSessionDidFinishEventsForBackgroundURLSession:(NSURLSession *)session
{
    MyInf(@"enter");
/*
    AppDelegate *appDelegate = (AppDelegate *)[[UIApplication sharedApplication] delegate];
    if (appDelegate.backgroundSessionCompletionHandler) {
        void (^completionHandler)() = appDelegate.backgroundSessionCompletionHandler;
        appDelegate.backgroundSessionCompletionHandler = nil;
        [[NSOperationQueue mainQueue] addOperationWithBlock:^{
            MyInf(@"call completionHandler");
            completionHandler();
        }];
    }

//    if (appDelegate.wakeupByUrlSession) {
        dispatch_async(dispatch_get_main_queue(), ^{
            [self invalidBackgroundSession];
        });
//    }
 */
}

/*
 Location Functions
 */



- (void)startLocationMonitor {
    if (self.LocationManager_monitor)
        [self.LocationManager_monitor stopMonitoringSignificantLocationChanges];
    self.LocationManager_monitor = [[CLLocationManager alloc]init];
    self.LocationManager_monitor.delegate = self;
    self.LocationManager_monitor.desiredAccuracy = kCLLocationAccuracyBest;
    self.LocationManager_monitor.activityType = CLActivityTypeFitness;
    self.LocationManager_monitor.pausesLocationUpdatesAutomatically = NO;
    if(IS_OS_9_OR_LATER) {
        self.LocationManager_monitor.allowsBackgroundLocationUpdates = YES;
    }
    if(IS_OS_8_OR_LATER) {
        [self.LocationManager_monitor requestAlwaysAuthorization];
    }
    [self.LocationManager_monitor startMonitoringSignificantLocationChanges];
    MyInf(@"startMonitoringLocation\n");
}

- (void)startLocationUpdate {
    if (self.LocationManager_update)
        [self.LocationManager_update stopUpdatingLocation];
    self.deferringUpdates = NO;
    self.LocationManager_update = [[CLLocationManager alloc]init];
    self.LocationManager_update.delegate = self;
    self.LocationManager_update.desiredAccuracy = kCLLocationAccuracyHundredMeters;
    self.LocationManager_update.activityType = CLActivityTypeFitness;
    //    _anotherLocationManager.distanceFilter = kCLDistanceFilterNone;
    self.LocationManager_update.distanceFilter = 1000;
    self.LocationManager_update.pausesLocationUpdatesAutomatically = NO;
    if(IS_OS_9_OR_LATER) {
        self.LocationManager_update.allowsBackgroundLocationUpdates = YES;
    }
    if(IS_OS_8_OR_LATER) {
        [self.LocationManager_update requestAlwaysAuthorization];
    }
    [self.LocationManager_update startUpdatingLocation];
    MyInf(@"startUpdatingLocation\n");
}

- (void)stopLocationUpdate {
    if (self.LocationManager_update)
        [self.LocationManager_update stopUpdatingLocation];
    self.LocationManager_update = nil;
}

/*
//#define DISABLE_LOCATION
#define LOCATION_SIG
- (void)startMonitoringLocation {
#ifdef DISABLE_LOCATION
    return;
#endif
    if (_anotherLocationManager) {
#ifdef LOCATION_SIG
        [_anotherLocationManager stopMonitoringSignificantLocationChanges];
#else
        [_anotherLocationManager stopUpdatingLocation];
#endif
    }
    self.deferringUpdates = NO;
    
    self.anotherLocationManager = [[CLLocationManager alloc]init];
    _anotherLocationManager.delegate = self;
    _anotherLocationManager.desiredAccuracy = kCLLocationAccuracyBest;
    _anotherLocationManager.activityType = CLActivityTypeFitness;
#ifndef LOCATION_SIG
//    _anotherLocationManager.distanceFilter = kCLDistanceFilterNone;
    _anotherLocationManager.distanceFilter = 50;
#endif
    _anotherLocationManager.pausesLocationUpdatesAutomatically = NO;
    if(IS_OS_9_OR_LATER) {
        _anotherLocationManager.allowsBackgroundLocationUpdates = YES;
    }
    if(IS_OS_8_OR_LATER) {
        [_anotherLocationManager requestAlwaysAuthorization];
    }
#ifdef LOCATION_SIG
    [_anotherLocationManager startMonitoringSignificantLocationChanges];
#else
    [_anotherLocationManager startUpdatingLocation];
#endif
    MyInf(@"startMonitoringLocation\n");
}
#endif
*/
 
#pragma mark - CLLocationManager Delegate

- (void)locationManager:(CLLocationManager *)manager didUpdateLocations:(NSArray *)locations
{
    
    MyInf(@"locationManager didUpdateLocations: %@",locations);
    for (int i = 0; i < locations.count; i++) {
//        CLLocation * newLocation = [locations objectAtIndex:i];
//        CLLocationCoordinate2D theLocation = newLocation.coordinate;
//        CLLocationAccuracy theAccuracy = newLocation.horizontalAccuracy;
        
//        self.myLocation = theLocation;
//        self.myLocationAccuracy = theAccuracy;
//        MyDbg(@"new location: %@", newLocation);
    }
/*
    if (!self.deferringUpdates) {
        CLLocationDistance distance = 10;
        NSTimeInterval time = 33;
        [_anotherLocationManager allowDeferredLocationUpdatesUntilTraveled:distance
                                                           timeout:time];
        self.deferringUpdates = YES;
    }
*/
}

-(void)locationManager:(CLLocationManager *)manager didFinishDeferredUpdatesWithError:(NSError *)error
{
    MyInf(@"finish defer update, error = %@", error);
    // Stop deferring updates
    self.deferringUpdates = NO;
    // Adjust for the next goal
}

@end




////////////////// C functions ////////////////////////////////////
/*
 Common functions
 */
NSString *MyLib_DeviceUUID(void)
{
    NSLog(@"device id: %@", [[UIDevice currentDevice] identifierForVendor]);
    return [[[UIDevice currentDevice] identifierForVendor] UUIDString];
}

/*
 Hash Functions
 */
#import <CommonCrypto/CommonDigest.h>

NSString *MyLib_DataMD5(NSData *data)
{
    unsigned char result[CC_MD5_DIGEST_LENGTH];
    CC_MD5( data.bytes, (int)data.length, result ); // This is the md5 call
    return [NSString stringWithFormat:
            @"%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x%02x",
            result[0], result[1], result[2], result[3],
            result[4], result[5], result[6], result[7],
            result[8], result[9], result[10], result[11],
            result[12], result[13], result[14], result[15]
            ];  
}

/*
 Date Functions
 */
NSString *MyLib_DateString_YMDHms(NSDate *date)
{
    NSDateFormatter *dateFormatter = [[NSDateFormatter alloc] init];
    [dateFormatter setDateFormat:@"yyyy-MM-dd HH:mm:ss"];
    return [dateFormatter stringFromDate:date];
}

NSString *MyLib_DateString_YM(NSDate *date)
{
    NSDateFormatter *dateFormatter = [[NSDateFormatter alloc] init];
    [dateFormatter setDateFormat:@"yyyy-MM"];
    return [dateFormatter stringFromDate:date];
}

NSDate *MyLib_StringDate(NSString *string)
{
    NSDateFormatter *dateFormatter = [[NSDateFormatter alloc] init];
    [dateFormatter setDateFormat:@"yyyy-MM-dd HH:mm:ss"];
    return [dateFormatter dateFromString:string];
}

NSUInteger MyLib_UnixTimeUs(void)
{
    return (NSUInteger)([[NSDate date] timeIntervalSince1970]*1000);
}


NSString *MyLib_UnixTimeUsString(void)
{
    return [NSString stringWithFormat:@"%ld", MyLib_UnixTimeUs()];
}

/* image functions */
UIImage *MyLib_ImageWithImage(UIImage *image, CGSize newSize)
{
    // Create a graphics image context
    UIGraphicsBeginImageContext(newSize);
    
    // Tell the old image to draw in this new context, with the desired
    // new size
    [image drawInRect:CGRectMake(0,0,newSize.width,newSize.height)];
    // Get the new image from the context
    UIImage* newImage = UIGraphicsGetImageFromCurrentImageContext();
    // End the context
    UIGraphicsEndImageContext();
    // Return the new image.
    return newImage;
}

UIImage *MyLib_ImageWithImage_SameAspectRatio(UIImage *sourceImage, CGSize targetSize)
{
    CGSize imageSize = sourceImage.size;
    CGFloat width = imageSize.width;
    CGFloat height = imageSize.height;
    CGFloat targetWidth = targetSize.width;
    CGFloat targetHeight = targetSize.height;
    CGFloat scaleFactor = 0.0;
    CGFloat scaledWidth = targetWidth;
    CGFloat scaledHeight = targetHeight;
    CGPoint thumbnailPoint = CGPointMake(0.0,0.0);
    
    if (CGSizeEqualToSize(imageSize, targetSize) == NO) {
        CGFloat widthFactor = targetWidth / width;
        CGFloat heightFactor = targetHeight / height;
        
        if (widthFactor > heightFactor) {
            scaleFactor = widthFactor; // scale to fit height
        }
        else {
            scaleFactor = heightFactor; // scale to fit width
        }
        
        scaledWidth  = width * scaleFactor;
        scaledHeight = height * scaleFactor;
        
        // center the image
        if (widthFactor > heightFactor) {
            thumbnailPoint.y = (targetHeight - scaledHeight) * 0.5;
        }
        else if (widthFactor < heightFactor) {
            thumbnailPoint.x = (targetWidth - scaledWidth) * 0.5;
        }
    }
    
    CGImageRef imageRef = [sourceImage CGImage];
    CGBitmapInfo bitmapInfo = CGImageGetBitmapInfo(imageRef);
    CGColorSpaceRef colorSpaceInfo = CGImageGetColorSpace(imageRef);
    
    if (bitmapInfo == kCGImageAlphaNone) {
        bitmapInfo = kCGImageAlphaNoneSkipLast;
    }
    
    CGContextRef bitmap;
    
    if (sourceImage.imageOrientation == UIImageOrientationUp || sourceImage.imageOrientation == UIImageOrientationDown) {
        bitmap = CGBitmapContextCreate(NULL, targetWidth, targetHeight, CGImageGetBitsPerComponent(imageRef), CGImageGetBytesPerRow(imageRef), colorSpaceInfo, bitmapInfo);
        
    } else {
        bitmap = CGBitmapContextCreate(NULL, targetHeight, targetWidth, CGImageGetBitsPerComponent(imageRef), CGImageGetBytesPerRow(imageRef), colorSpaceInfo, bitmapInfo);
        
    }
    
    // In the right or left cases, we need to switch scaledWidth and scaledHeight,
    // and also the thumbnail point
    if (sourceImage.imageOrientation == UIImageOrientationLeft) {
        thumbnailPoint = CGPointMake(thumbnailPoint.y, thumbnailPoint.x);
        CGFloat oldScaledWidth = scaledWidth;
        scaledWidth = scaledHeight;
        scaledHeight = oldScaledWidth;
        
        CGContextRotateCTM (bitmap, M_PI_2); // + 90 degrees
        CGContextTranslateCTM (bitmap, 0, -targetHeight);
        
    } else if (sourceImage.imageOrientation == UIImageOrientationRight) {
        thumbnailPoint = CGPointMake(thumbnailPoint.y, thumbnailPoint.x);
        CGFloat oldScaledWidth = scaledWidth;
        scaledWidth = scaledHeight;
        scaledHeight = oldScaledWidth;
        
        CGContextRotateCTM (bitmap, -M_PI_2); // - 90 degrees
        CGContextTranslateCTM (bitmap, -targetWidth, 0);
        
    } else if (sourceImage.imageOrientation == UIImageOrientationUp) {
        // NOTHING
    } else if (sourceImage.imageOrientation == UIImageOrientationDown) {
        CGContextTranslateCTM (bitmap, targetWidth, targetHeight);
        CGContextRotateCTM (bitmap, -M_PI); // - 180 degrees
    }
    
    CGContextDrawImage(bitmap, CGRectMake(thumbnailPoint.x, thumbnailPoint.y, scaledWidth, scaledHeight), imageRef);
    CGImageRef ref = CGBitmapContextCreateImage(bitmap);
    UIImage* newImage = [UIImage imageWithCGImage:ref];
    
    CGContextRelease(bitmap);
    CGImageRelease(ref);
    
    return newImage; 
}


/*
 XML functions
 */

NSMutableDictionary *MyLib_SimpleXMLDict(NSData *xmlData)
{
    NSMutableDictionary *resultDict = [[NSMutableDictionary alloc] init];
    NSError *error = nil;
    NSDictionary *resp = [XMLReader dictionaryForXMLData:xmlData
                                                 options:XMLReaderOptionsProcessNamespaces
                                                   error:&error];
    if (!resp)
        return nil;

    NSDictionary *tmp = [resp allValues][0];
//    NSLog(@"%@\n", tmp);
    
    [tmp enumerateKeysAndObjectsUsingBlock:^(id key, id obj, BOOL *stop) {
//        NSLog(@"obj: %@, want: %@\n", [obj class], [NSMutableDictionary class]);
        if ([obj isKindOfClass:[NSMutableDictionary class]]) /*ignore complex xml*/
            [resultDict setObject:[obj valueForKey:@"text"] forKey:key];
    }];
    return resultDict;
}

/* base64 */

NSString *base64_encode(NSString *str)
{
    NSData *plainData = [str dataUsingEncoding:NSUTF8StringEncoding];
    NSString *base64String = [plainData base64EncodedStringWithOptions:0];
    return base64String;
}

NSString *base64_decode(NSString *str)
{
    NSData *decodedData = [[NSData alloc] initWithBase64EncodedString:str options:0];
    NSString *decodedString = [[NSString alloc] initWithData:decodedData encoding:NSUTF8StringEncoding];
    return decodedString;
}

/* json */
NSMutableDictionary *json_to_dict(NSString *strJson)
{
    NSMutableDictionary *resDict = [[NSMutableDictionary alloc] init];
    NSError *e = nil;
    id json = [NSJSONSerialization JSONObjectWithData:[strJson dataUsingEncoding:NSUTF8StringEncoding] options: NSJSONReadingMutableContainers error: &e];
    if (!e && ([json isKindOfClass:[NSDictionary class]] || [json isKindOfClass:[NSMutableDictionary class]])) {
        resDict = json;
    }
    return resDict;
}


/*
 #################################################################
 Log Functions
 #################################################################
 */

NSString* MyLib_LogFilePath(void)
{
    NSString *docDir = [NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES) objectAtIndex:0];
    NSString *fullPath = [NSString stringWithFormat:@"%@/%@", docDir, @"log.txt"];
    return fullPath;
}

/* call this function in main() */
void MyLib_Log2File(void)
{
    NSString *fullPath = MyLib_LogFilePath();
    freopen([fullPath cStringUsingEncoding:NSASCIIStringEncoding], "a+", stdout);
    freopen([fullPath cStringUsingEncoding:NSASCIIStringEncoding], "a+", stderr);
}


